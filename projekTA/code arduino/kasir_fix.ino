/*
  ESP32 + RC522: Push UID ke server (rfid_latest + rfid_scans) agar POS (pos.php)
  bisa mengambilnya lewat get_uid.php. Opsional: uji transaksi via serial:
  Kirim: BUY <barang_id> <qty>  (butuh uid terakhir tersimpan)

  DB rujukan:
  - rfid_latest(id=1, uid, seen_at)        -> UID terakhir terbaca
  - rfid_scans(uid, device_id, idempotency_key, note) -> log scan
  - santri(rfid_uid, saldo, aktif)         -> cocokan uid dan saldo/aktif
  - transaksi / transaksi_items / barang   -> pembelian & update stok
*/

#include <WiFi.h>
#include <HTTPClient.h>
#include <SPI.h>
#include <MFRC522.h>

// === BUZZER (Active buzzer) ===
#define BUZZER_PIN 21        // ubah pin sesuai wiring
#define BUZZER_ACTIVE_HIGH 1 // 1: HIGH=ON, 0: LOW=ON


// ==== Hardware RC522 (sesuaikan jika perlu) ====
#define SS_PIN   5
#define RST_PIN 22
MFRC522 mfrc522(SS_PIN, RST_PIN);

// ==== WiFi ====
const char* WIFI_SSID = "MTsbu Selatan";
const char* WIFI_PASS = "@BPMtsbu104.";

// ==== Server ====
const char* SERVER_HOST = "192.168.0.119"; // IP/domain server
const uint16_t SERVER_PORT = 80;
const char* BASE_PATH   = "/projekTA";     // kosongkan "" jika aplikasi di root

// Endpoint untuk push UID terbaru (harap arahkannya ke script yang meng-UPDATE rfid_latest
// dan INSERT ke rfid_scans). Misal kamu sudah punya "save_card.php" di project lama.
const char* API_SAVE_UID = "/api/save_card.php";

// Endpoint API beli sesuai web POS kamu (pos.php memanggil ./api/beli.php)
const char* API_BELI     = "/api/beli.php";

// ==== Opsi ====
const bool ENABLE_SERIAL_BUY_TEST = true;  // true: aktifkan perintah serial "BUY <id_barang> <qty>"
const unsigned long RESCAN_COOLDOWN_MS = 1500; // jeda antar-scan untuk anti double

// ==== State ====
String lastUID = "";
unsigned long lastScanAt = 0;

// ==== Util ====
String httpBase() {
  String b = "http://";
  b += SERVER_HOST;
  if (SERVER_PORT != 80) { b += ":" + String(SERVER_PORT); }
  if (String(BASE_PATH).length() > 0) b += String(BASE_PATH);
  return b;
}

// Dapatkan "device_id" sederhana dari MAC
// --- sebelum (yang error) ---
// esp_read_mac(mac, ESP_MAC_WIFI_STA);

// --- sesudah (aman di ESP32 & tidak tergantung IDF) ---
String getDeviceId() {
  uint8_t mac[6];
  WiFi.macAddress(mac);  // ambil MAC STA
  char buf[20];
  snprintf(buf, sizeof(buf), "ESP32-%02X%02X%02X", mac[3], mac[4], mac[5]);
  return String(buf);
}

String makeIdempotencyKey() {
  uint8_t mac[6];
  WiFi.macAddress(mac);
  char buf[40];
  snprintf(buf, sizeof(buf), "%lu-%02X%02X%02X",
           (unsigned long)millis(), mac[3], mac[4], mac[5]);
  return String(buf);
}


// POST x-www-form-urlencoded sederhana
bool httpPostForm(const String& url, const String& formBody, String &resp, int &code) {
  HTTPClient http;
  http.setTimeout(6000);
  http.begin(url);
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");
  code = http.POST(formBody);
  resp = http.getString();
  http.end();
  return (code > 0);
}

// Kirim UID terbaru ke server -> untuk diambil pos.php lewat get_uid.php
bool pushUIDToServer(const String& uid) {
  String url  = httpBase() + String(API_SAVE_UID); // contoh: http://IP/projekTA/api/save_card.php
  String form = "uid=" + uid +
                "&device_id=" + getDeviceId() +
                "&idempotency_key=" + makeIdempotencyKey() +
                "&note=scan";

  Serial.println("[HTTP] POST " + url);
  String resp; int code;
  if (!httpPostForm(url, form, resp, code)) {
    Serial.println("[HTTP] gagal kirim (koneksi).");
    return false;
  }
  Serial.printf("[HTTP %d] %s\n", code, resp.c_str());
  return (code >= 200 && code < 300);
}

// Uji langsung API beli.php (opsional, untuk debug dari serial monitor)
void buyViaApi(const String& uid, int barangId, int qty) {
  if (uid.length() == 0) {
    Serial.println("[BUY] UID kosong. Scan kartu dulu.");
    return;
  }
  String url  = httpBase() + String(API_BELI);     // contoh: http://IP/projekTA/api/beli.php
  String form = "uid=" + uid + "&barang_id=" + String(barangId) + "&qty=" + String(qty);

  Serial.println("[BUY] POST " + url + " -> " + form);
  String resp; int code;
  if (!httpPostForm(url, form, resp, code)) {
    Serial.println("[BUY] Gagal kirim (koneksi).");
    return;
  }
  Serial.printf("[BUY HTTP %d] %s\n", code, resp.c_str());
}

// ==== Arduino ====
void connectWiFi() {
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASS);
  Serial.print("WiFi connecting");
  uint8_t tries = 0;
  while (WiFi.status() != WL_CONNECTED && tries < 40) {
    delay(500);
    Serial.print(".");
    tries++;
  }
  Serial.println();
  if (WiFi.status() == WL_CONNECTED) {
    Serial.print("WiFi OK, IP: ");
    Serial.println(WiFi.localIP());
  } else {
    Serial.println("WiFi gagal. Akan dicoba lagi di loop.");
  }
}



// === Buzzer helpers ===
void buzzerWrite(bool on) {
  digitalWrite(BUZZER_PIN, BUZZER_ACTIVE_HIGH ? (on ? HIGH : LOW) : (on ? LOW : HIGH));
}
void beep(int onMs=50, int offMs=0) {
  buzzerWrite(true);
  delay(onMs);
  buzzerWrite(false);
  if (offMs>0) delay(offMs);
}
void beepSuccess() { beep(150, 0); }
void beepError()   { for (int i=0;i<3;i++){ beep(70, 70);} }

void setup() {
  // Buzzer init
  pinMode(BUZZER_PIN, OUTPUT);
  buzzerWrite(false);

  Serial.begin(115200);
  delay(400);
  SPI.begin();
  mfrc522.PCD_Init();
  connectWiFi();
  Serial.println("Siap. Tempelkan kartu...");
}

void handleSerialCommand() {
  if (!ENABLE_SERIAL_BUY_TEST) return;
  if (!Serial.available()) return;

  String line = Serial.readStringUntil('\n');
  line.trim();
  if (line.length() == 0) return;

  // Format: BUY <id_barang> <qty>
  if (line.startsWith("BUY") || line.startsWith("buy")) {
    int sp1 = line.indexOf(' ');
    int sp2 = line.indexOf(' ', sp1 + 1);
    if (sp1 > 0 && sp2 > sp1) {
      int idb = line.substring(sp1 + 1, sp2).toInt();
      int qty = line.substring(sp2 + 1).toInt();
      if (idb > 0 && qty > 0) {
        buyViaApi(lastUID, idb, qty);
      } else {
        Serial.println("Format: BUY <id_barang> <qty>");
      }
    } else {
      Serial.println("Format: BUY <id_barang> <qty>");
    }
  } else {
    Serial.println("Perintah tak dikenali. Contoh: BUY 1 1");
  }
}

void loop() {
  if (WiFi.status() != WL_CONNECTED) {
    connectWiFi();
  }

  handleSerialCommand();

  // Baca kartu
  if (!mfrc522.PICC_IsNewCardPresent()) return;
  if (!mfrc522.PICC_ReadCardSerial())   return;

  // Anti double-scan terlalu cepat
  if (millis() - lastScanAt < RESCAN_COOLDOWN_MS) {
    mfrc522.PICC_HaltA();
    mfrc522.PCD_StopCrypto1();
    return;
  }

  // Bentuk UID HEX uppercase tanpa spasi, mis: 43217D28
  String uid = "";
  for (byte i = 0; i < mfrc522.uid.size; i++) {
    if (mfrc522.uid.uidByte[i] < 0x10) uid += "0";
    uid += String(mfrc522.uid.uidByte[i], HEX);
  }
  uid.toUpperCase();

  lastUID   = uid;
  lastScanAt = millis();

  Serial.print("UID: ");
  Serial.println(uid);
  beepSuccess();


  // Dorong UID ke server agar pos.php bisa membacanya via get_uid.php
  if (!pushUIDToServer(uid)) {
    beepError();
  }


  // Hentikan komunikasi kartu
  mfrc522.PICC_HaltA();
  mfrc522.PCD_StopCrypto1();
}
