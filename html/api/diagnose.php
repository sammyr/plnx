<?php
header('Content-Type: application/json');

// Funktion um Shell-Befehle sicher auszuführen
function executeCommand($command) {
    $output = [];
    $returnCode = 0;
    exec($command . ' 2>&1', $output, $returnCode);
    return [
        'output' => implode("\n", $output),
        'code' => $returnCode
    ];
}

// SRS Stream-Informationen abrufen
function getSRSStreams() {
    $srsApiUrl = 'http://srs:1985/api/v1/streams/';
    
    $ch = curl_init($srsApiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        return [
            'status' => 'ok',
            'stream_count' => count($data['streams'] ?? []),
            'total_clients' => array_sum(array_column($data['streams'] ?? [], 'clients')),
            'streams' => $data['streams'] ?? []
        ];
    }
    
    return [
        'status' => 'error',
        'stream_count' => 0,
        'total_clients' => 0,
        'streams' => []
    ];
}

// FFmpeg Prozesse zählen
function getFFmpegProcesses() {
    $result = executeCommand('docker exec $(docker ps -q --filter "name=auto-hls") ps aux | grep ffmpeg | grep -v grep | wc -l');
    return [
        'process_count' => (int)trim($result['output']),
        'status' => $result['code'] === 0 ? 'ok' : 'error'
    ];
}

// Docker Container Status
function getContainerStatus() {
    $containers = [
        'nginx',
        'php-fpm',
        'srs',
        'auto-hls',
        'webrtc-signaling'
    ];
    
    $status = [];
    foreach ($containers as $container) {
        $result = executeCommand('docker ps --filter "name=' . $container . '" --format "{{.Status}}"');
        $isRunning = strpos($result['output'], 'Up') !== false;
        
        $status[] = [
            'name' => $container,
            'status' => $isRunning ? 'running' : 'stopped'
        ];
    }
    
    return $status;
}

// System-Ressourcen
function getSystemResources() {
    // CPU
    $cpuResult = executeCommand('top -bn1 | grep "Cpu(s)" | sed "s/.*, *\\([0-9.]*\\)%* id.*/\\1/" | awk \'{print 100 - $1"%"}\'');
    
    // Memory
    $memResult = executeCommand('free -h | awk \'/^Mem:/ {print $3 "/" $2}\'');
    
    // Disk
    $diskResult = executeCommand('df -h / | awk \'NR==2 {print $3 "/" $2 " (" $5 ")"}\'');
    
    return [
        'cpu' => trim($cpuResult['output']) ?: 'N/A',
        'memory' => trim($memResult['output']) ?: 'N/A',
        'disk' => trim($diskResult['output']) ?: 'N/A'
    ];
}

// Logs sammeln
function getRecentLogs() {
    $logs = [];
    
    // SRS Logs
    $srsLogs = executeCommand('docker logs $(docker ps -q --filter "name=srs") --tail 10 2>&1');
    $logs[] = "=== SRS Logs ===\n" . $srsLogs['output'];
    
    // Auto-HLS Logs
    $hlsLogs = executeCommand('docker logs $(docker ps -q --filter "name=auto-hls") --tail 10 2>&1');
    $logs[] = "\n=== Auto-HLS Logs ===\n" . $hlsLogs['output'];
    
    return implode("\n", $logs);
}

// Sammle alle Diagnosedaten
$diagnoseData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'srs' => getSRSStreams(),
    'ffmpeg' => getFFmpegProcesses(),
    'containers' => getContainerStatus(),
    'system' => getSystemResources(),
    'logs' => getRecentLogs()
];

echo json_encode($diagnoseData, JSON_PRETTY_PRINT);
