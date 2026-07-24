<?php
header('Content-Type: application/json');

// misalnya UID terakhir disimpan di file uid.txt oleh ESP32
$filename = __DIR__ . "/latest_uid.txt";

// default response
$response = [
    "status" => "waiting",
    "uid" => ""
];

if (file_exists($filename)) {
    $uid = trim(file_get_contents($filename));

    if ($uid !== "") {
        $response["status"] = "success";
        $response["uid"] = $uid;
    }
}

echo json_encode($response);
