<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$roomId = $_GET['room'] ?? 'demo_video_stream';

// Video-Informationen basierend auf Room-ID
$videoInfo = [
    'room_id' => $roomId,
    'title' => ucwords(str_replace('_', ' ', $roomId)),
    'duration' => '42:30',
    'file_size' => '153 MB',
    'format' => 'MP4',
    'resolution' => '1920x1080',
    'fps' => 60,
    'bitrate' => '5000 kbps',
    'codec' => 'H.264',
    'audio_codec' => 'AAC',
    'viewers' => rand(1000, 1500),
    'price_per_hour' => 49.99,
    'currency' => 'EUR',
    'status' => 'live',
    'started_at' => date('Y-m-d H:i:s', strtotime('-30 minutes')),
    'current_time' => date('H:i:s'),
    'current_date' => date('l, d. F Y')
];

echo json_encode([
    'success' => true,
    'data' => $videoInfo,
    'timestamp' => time()
]);
?>
