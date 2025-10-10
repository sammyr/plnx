<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$response = [
    'status' => 'success',
    'message' => 'PHP läuft erfolgreich!',
    'php_version' => phpversion(),
    'server_time' => date('Y-m-d H:i:s'),
    'loaded_extensions' => get_loaded_extensions()
];

echo json_encode($response, JSON_PRETTY_PRINT);
?>
