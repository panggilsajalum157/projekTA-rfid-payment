<?php
// api/config.php (fixed)
// XAMPP default: user root, password kosong. Ubah jika perlu.

mysqli_report(MYSQLI_REPORT_OFF);

$DB_HOST = 'localhost';
$DB_USER = 'root';    // Ubah kalau bukan root
$DB_PASS = '';        // Isi kalau MySQL-mu pakai password
$DB_NAME = 'koperasi_pp';

$mysqli = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($mysqli->connect_errno) {
  // Jika dipanggil dari /api/* balas JSON; selain itu tampilkan HTML error rapih
  $is_api = (strpos($_SERVER['SCRIPT_NAME'], '/api/') !== false)
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
  if ($is_api) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'DB connect error: '.$mysqli->connect_error]);
  } else {
    http_response_code(500);
    echo '<!doctype html><meta charset="utf-8"><style>body{font-family:system-ui;background:#0f172a;color:#e2e8f0;padding:20px}</style><h2>DB connect error</h2><pre>'
         . htmlspecialchars($mysqli->connect_error, ENT_QUOTES, 'UTF-8')
         . '</pre>';
  }
  exit;
}

$mysqli->set_charset('utf8mb4');
$mysqli->query("SET time_zone = '+00:00'");
// Alias agar file lain yang memakai $conn tetap jalan
$conn = $mysqli;

