<?php
// api/auth.php
session_start();
require __DIR__.'/config.php';

// ambil data POST biasa
if(!isset($_POST['username']) || !isset($_POST['password'])) { 
    echo json_encode(['status'=>'error','message'=>'Data tidak lengkap']); 
    exit; 
}

$username = $_POST['username'];
$password = $_POST['password'];

// query cek user
$stmt = $mysqli->prepare("SELECT id,username,password_hash,role,nama FROM users WHERE username=? LIMIT 1");
$stmt->bind_param('s', $username);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) { 
    echo json_encode(['status'=>'failed','message'=>'User tidak ditemukan']); 
    exit; 
}

$user = $res->fetch_assoc();

// verifikasi password
if (password_verify($password, $user['password_hash'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];

    echo json_encode([
        'status'=>'success',
        'user'=>[
            'id'=>$user['id'],
            'username'=>$user['username'],
            'role'=>$user['role'],
            'nama'=>$user['nama']
        ]
    ]);
} else {
    echo json_encode(['status'=>'failed','message'=>'Password salah']);
}
