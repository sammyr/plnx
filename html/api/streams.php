<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Simuliere verfügbare Streams
$streams = [
    [
        'id' => 'demo_video_stream',
        'title' => 'Demo Video Stream',
        'viewers' => rand(1000, 1500),
        'duration' => '42:30',
        'quality' => '4K',
        'price' => 49.99,
        'thumbnail' => 'videos/ttr.m4v',
        'status' => 'live'
    ],
    [
        'id' => 'premium_stream_1',
        'title' => 'Premium Live Event',
        'viewers' => rand(500, 800),
        'duration' => '01:15:00',
        'quality' => '4K',
        'price' => 79.99,
        'thumbnail' => 'videos/ttr.m4v',
        'status' => 'live'
    ]
];

echo json_encode([
    'success' => true,
    'streams' => $streams,
    'total' => count($streams),
    'timestamp' => time()
]);
?>
