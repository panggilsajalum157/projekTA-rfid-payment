<?php
// api/save_card.php
header('Content-Type: application/json');

// Pakai config yang sama dengan file lain
require_once __DIR__ . '/config.php';

try {
  // Ambil dari POST atau GET
  $uid   = isset($_POST['uid'])   ? trim($_POST['uid'])   : (isset($_GET['uid'])   ? trim($_GET['uid'])   : '');
  $nama  = isset($_POST['nama'])  ? trim($_POST['nama'])  : (isset($_GET['nama'])  ? trim($_GET['nama'])  : '');
  $saldo = isset($_POST['saldo']) ? (int)$_POST['saldo']  : (isset($_GET['saldo']) ? (int)$_GET['saldo']  : 0);

  if ($uid === '') {
    echo json_encode(['status'=>'error','message'=>'UID kosong']); exit;
  }

  // Simpan juga ke file (opsional, untuk debugging/kompatibilitas)


  // Upsert ke rfid_latest (id=1)
  $stmt = $mysqli->prepare("
    INSERT INTO rfid_latest (id, uid, seen_at)
    VALUES (1, ?, NOW())
    ON DUPLICATE KEY UPDATE uid=VALUES(uid), seen_at=VALUES(seen_at)
  ");
  $stmt->bind_param('s', $uid);
  $stmt->execute();

  // Kalau hanya scan (tidak daftar)
  if ($nama === '') {
    echo json_encode(['status'=>'success','message'=>'UID tersimpan sementara','uid'=>$uid]);
    exit;
  }

  // Proses pendaftaran kartu bila nama diisi
  $cek = $mysqli->prepare("SELECT id FROM santri WHERE rfid_uid=? LIMIT 1");
  $cek->bind_param('s', $uid);
  $cek->execute();
  if ($cek->get_result()->fetch_assoc()) {
    echo json_encode(['status'=>'error','message'=>'UID sudah terdaftar']); exit;
  }

  $ins = $mysqli->prepare("INSERT INTO santri (rfid_uid, nama, saldo) VALUES (?, ?, ?)");
  $ins->bind_param('ssi', $uid, $nama, $saldo);
  if ($ins->execute()) {
    echo json_encode(['status'=>'success','message'=>'Kartu berhasil didaftarkan!','uid'=>$uid]);
  } else {
    echo json_encode(['status'=>'error','message'=>'Gagal simpan santri']);
  }
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['status'=>'error','message'=>'Server error: '.$e->getMessage()]);
}
