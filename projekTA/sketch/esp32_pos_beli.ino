#include <WiFi.h>
#include <HTTPClient.h>
#include <SPI.h>
#include <MFRC522.h>

#define SS_PIN 21
#define RST_PIN 22
MFRC522 rfid(SS_PIN, RST_PIN);

const char* ssid = "YOUR_SSID";
const char* password = "YOUR_PASS";
String beliUrl = "http://YOUR_SERVER/koperasi_rfid/api/beli.php";

// default item to buy (ubah sesuai kebutuhan)
const int DEFAULT_BARANG_ID = 1;
const int DEFAULT_QTY = 1;

void setup() {
  Serial.begin(115200);
  SPI.begin();
  rfid.PCD_Init();
  WiFi.begin(ssid, password);
  Serial.print("WiFi...");
  while (WiFi.status() != WL_CONNECTED) { delay(500); Serial.print('.'); }
  Serial.println(" connected");
}

String uidHex(MFRC522::Uid uid) {
  String s="";
  for (byte i=0;i<uid.size;i++){
    if(uid.uidByte[i] < 0x10) s += "0";
    s += String(uid.uidByte[i], HEX);
  }
  s.toUpperCase();
  return s;
}

unsigned long last = 0;
void loop() {
  if (!rfid.PICC_IsNewCardPresent()) return;
  if (!rfid.PICC_ReadCardSerial()) return;
  String uid = uidHex(rfid.uid);
  Serial.println("UID: "+uid);
  if (millis() - last < 1200) { rfid.PICC_HaltA(); rfid.PCD_StopCrypto1(); return; }
  last = millis();

  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    WiFiClient client;
    http.begin(client, beliUrl);
    http.addHeader("Content-Type","application/x-www-form-urlencoded");
    String body = "uid="+uid+"&barang_id="+String(DEFAULT_BARANG_ID)+"&qty="+String(DEFAULT_QTY);
    int code = http.POST(body);
    Serial.println("HTTP code: "+String(code));
    if (code>0) {
      String res = http.getString();
      Serial.println("Res: "+res);
    }
    http.end();
  } else Serial.println("WiFi disconnect");

  rfid.PICC_HaltA(); rfid.PCD_StopCrypto1();
  delay(200);
}
