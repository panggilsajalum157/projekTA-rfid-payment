<?php
require __DIR__.'/config.php';
require __DIR__.'/utils.php';

$uid = trim($_POST['uid'] ?? '');

if ($uid === '') {
    json_err('uid kosong');
}

$stmt = $mysqli->prepare("UPDATE rfid_latest SET uid=?, seen_at=NOW() WHERE id=1");
$stmt->bind_param('s', $uid);

if ($stmt->execute()) {
    json_ok([
        'uid'     => $uid,
        'seen_at' => date('Y-m-d H:i:s')
    ]);
} else {
    json_err($mysqli->error, 500);
}
