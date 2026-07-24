<?php
// web/get_uid.php
header('Content-Type: application/json');
require_once __DIR__ . '/../api/config.php';

try {
  // 1) Coba ambil dari DB
  $rs  = $mysqli->query("SELECT uid, seen_at FROM rfid_latest WHERE id=1 LIMIT 1");
  $row = $rs ? $rs->fetch_assoc() : null;
  $uid = $row && !empty($row['uid']) ? trim($row['uid']) : '';

  // 2) Fallback: bila DB kosong, baca file (opsional)
  if ($uid === '') {
    $file = __DIR__ . '/../api/latest_uid.txt';
    if (is_file($file)) {
      $uid = trim(@file_get_contents($file) ?: '');
    }
  }

  if ($uid === '') {
    echo json_encode(['status'=>'waiting','message'=>'Tempelkan kartu pada reader','uid'=>'']);
    exit;
  }

  // Cek santri
  $stmt = $mysqli->prepare("SELECT id, nama, saldo, aktif FROM santri WHERE rfid_uid=? LIMIT 1");
  $stmt->bind_param('s', $uid);
  $stmt->execute();
  $san = $stmt->get_result()->fetch_assoc();

  if (!$san) {
    echo json_encode([
      'status' => 'error',
      'message' => "UID $uid belum terdaftar",
      'uid' => $uid,
      'registered' => false
    ]);
    exit;
  }

  if ((int)$san['aktif'] !== 1) {
    echo json_encode([
      'status'=>'error',
      'message'=>"Akun santri nonaktif untuk UID $uid",
      'uid'=>$uid,
      'registered'=>true
    ]);
    exit;
  }

  // (opsional) catat scan
  $ins = $mysqli->prepare("INSERT INTO rfid_scans (uid, device_id, note) VALUES (?, 'web-pos', 'poll')");
  $ins->bind_param('s', $uid);
  @$ins->execute();

  echo json_encode([
    'status' => 'ok',
    'message' => "UID $uid terdeteksi",
    'uid' => $uid,
    'registered' => true,
    'santri' => [
      'id' => (int)$san['id'],
      'nama' => $san['nama'],
      'saldo' => (int)$san['saldo']
    ]
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['status'=>'error','message'=>'Server error: '.$e->getMessage()]);
}
