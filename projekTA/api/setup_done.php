<?php
// api/setup.php (jalankan sekali lalu hapus)
require __DIR__.'/config.php';
$msg='';
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  $nama = trim($_POST['nama'] ?? 'Administrator');
  if ($username === '' || $password === '') $msg='Username & password wajib';
  else {
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE username=? LIMIT 1");
    $stmt->bind_param('s',$username); $stmt->execute();
    $r = $stmt->get_result();
    if ($r->num_rows>0) $msg='Username sudah ada';
    else {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $ins = $mysqli->prepare("INSERT INTO users (username,password_hash,role,nama,created_at) VALUES (?,?,?,?,NOW())");
      $role='admin';
      $ins->bind_param('ssss',$username,$hash,$role,$nama);
      if ($ins->execute()) $msg='Admin dibuat. HAPUS setup.php untuk keamanan.'; else $msg='Gagal: '.$mysqli->error;
    }
  }
}
?>
<!doctype html><html><body>
<h2>Setup Admin</h2>
<?php if($msg) echo "<p><b>$msg</b></p>"; ?>
<form method="post">
<label>Username<br><input name="username"></label><br>
<label>Password<br><input name="password" type="password"></label><br>
<label>Nama<br><input name="nama"></label><br>
<button type="submit">Buat Admin</button>
</form>
</body></html>
