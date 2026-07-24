<?php
function json_ok($data = []) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok', 'data' => $data]);
    exit;
}

function json_err($message, $code = 400) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}
