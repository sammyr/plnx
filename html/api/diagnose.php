<?php
header('Content-Type: application/json');

// Hilfsfunktion zum Ausführen von Shell-Befehlen
function executeCommand($command) {
    // Docker-Pfad hinzufügen (für Webserver ohne Docker im PATH)
    $dockerPaths = [
        '/usr/bin/docker',
        '/usr/local/bin/docker',
        '/snap/bin/docker'
    ];
    
    $dockerPath = null;
    foreach ($dockerPaths as $path) {
        if (file_exists($path)) {
            $dockerPath = $path;
            break;
        }
    }
    
    // Wenn Docker-Pfad gefunden, ersetze im Befehl
    if ($dockerPath !== null) {
        // Ersetze 'docker' am Anfang oder nach Leerzeichen
        $command = preg_replace('/^docker\s/', $dockerPath . ' ', $command);
        $command = preg_replace('/\sdocker\s/', ' ' . $dockerPath . ' ', $command);
    }
    
    $output = '';
    $returnCode = 0;
    exec($command . ' 2>&1', $outputArray, $returnCode);
    $output = implode("\n", $outputArray);
    
    // Debug-Info hinzufügen
    return [
        'output' => $output, 
        'code' => $returnCode,
        'command' => $command,
        'docker_path' => $dockerPath
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
    $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    
    // Auf Windows: Mock-Daten
    if ($isWindows) {
        return [
            'process_count' => 0,
            'status' => 'unknown'
        ];
    }
    
    $result = executeCommand('docker exec $(docker ps -q --filter "name=auto-hls-vsgk4wcsoggo800w4ow48gos") ps aux | grep ffmpeg | grep -v grep | wc -l');
    return [
        'process_count' => (int)trim($result['output']),
        'status' => $result['code'] === 0 ? 'ok' : 'error'
    ];
}

// Docker Container Status
function getContainerStatus() {
    $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    
    $status = [];
    $projectId = 'vsgk4wcsoggo800w4ow48gos';
    
    // Prüfe ob Docker verfügbar ist
    $dockerAvailable = file_exists('/var/run/docker.sock') || file_exists('/usr/bin/docker');
    
    if (!$dockerAvailable) {
        // Docker nicht verfügbar - zeige erwartete Container mit Projekt-ID
        return [
            ['name' => 'nginx', 'status' => 'unknown', 'full_name' => 'nginx-' . $projectId],
            ['name' => 'php-fpm', 'status' => 'unknown', 'full_name' => 'php-fpm-' . $projectId],
            ['name' => 'srs', 'status' => 'unknown', 'full_name' => 'srs-' . $projectId],
            ['name' => 'auto-hls', 'status' => 'unknown', 'full_name' => 'auto-hls-' . $projectId],
            ['name' => 'webrtc-signaling', 'status' => 'unknown', 'full_name' => 'webrtc-signaling-' . $projectId]
        ];
    }
    
    if ($isWindows) {
        return [
            ['name' => 'nginx', 'status' => 'unknown'],
            ['name' => 'php-fpm', 'status' => 'unknown'],
            ['name' => 'srs', 'status' => 'unknown'],
            ['name' => 'auto-hls', 'status' => 'unknown'],
            ['name' => 'webrtc-signaling', 'status' => 'unknown']
        ];
    }
    
    $debugInfo = [];
    
    // Methode 1: docker ps mit JSON-Format (zuverlässiger)
    $result = executeCommand('docker ps -a --format "{{json .}}"');
    $debugInfo['method1_code'] = $result['code'];
    $debugInfo['method1_output'] = substr($result['output'], 0, 500);
    
    if ($result['code'] === 0 && !empty($result['output'])) {
        $lines = explode("\n", trim($result['output']));
        
        foreach ($lines as $line) {
            if (empty($line)) continue;
            
            $container = json_decode($line, true);
            if (!$container) continue;
            
            $containerName = $container['Names'] ?? '';
            
            // Nur Container dieses Projekts
            if (strpos($containerName, $projectId) !== false) {
                // Kürze Namen für Anzeige
                $displayName = str_replace('-' . $projectId, '', $containerName);
                $displayName = preg_replace('/-\d+$/', '', $displayName);
                
                // Status prüfen
                $containerStatus = $container['State'] ?? 'unknown';
                $isRunning = (strtolower($containerStatus) === 'running');
                
                $status[] = [
                    'name' => $displayName,
                    'status' => $isRunning ? 'running' : 'stopped',
                    'full_name' => $containerName,
                    'uptime' => $container['Status'] ?? 'N/A'
                ];
            }
        }
    }
    
    // Fallback: Wenn JSON nicht funktioniert, verwende docker inspect
    if (empty($status)) {
        // Hole alle Container-IDs
        $result = executeCommand('docker ps -aq');
        $debugInfo['method2_code'] = $result['code'];
        $debugInfo['method2_output'] = substr($result['output'], 0, 200);
        
        if ($result['code'] === 0 && !empty($result['output'])) {
            $containerIds = explode("\n", trim($result['output']));
            
            foreach ($containerIds as $containerId) {
                if (empty($containerId)) continue;
                
                // Inspect Container
                $inspectResult = executeCommand('docker inspect ' . escapeshellarg($containerId));
                
                if ($inspectResult['code'] === 0) {
                    $containerData = json_decode($inspectResult['output'], true);
                    
                    if ($containerData && isset($containerData[0])) {
                        $container = $containerData[0];
                        $containerName = ltrim($container['Name'] ?? '', '/');
                        
                        // Nur Container dieses Projekts
                        if (strpos($containerName, $projectId) !== false) {
                            $displayName = str_replace('-' . $projectId, '', $containerName);
                            $displayName = preg_replace('/-\d+$/', '', $displayName);
                            
                            $isRunning = ($container['State']['Running'] ?? false);
                            
                            $status[] = [
                                'name' => $displayName,
                                'status' => $isRunning ? 'running' : 'stopped',
                                'full_name' => $containerName,
                                'uptime' => $container['State']['Status'] ?? 'N/A'
                            ];
                        }
                    }
                }
            }
        }
    }
    
    // Wenn immer noch keine Container gefunden
    if (empty($status)) {
        // Füge Command-Debug-Info hinzu
        $debugInfo['docker_path_found'] = $result['docker_path'] ?? 'null';
        $debugInfo['last_command'] = $result['command'] ?? 'unknown';
        
        $status[] = [
            'name' => 'Keine Container gefunden',
            'status' => 'unknown',
            'debug' => json_encode($debugInfo, JSON_PRETTY_PRINT)
        ];
    }
    
    return $status;
}

// System-Ressourcen
function getSystemResources() {
    $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    
    if ($isWindows) {
        // Windows-Befehle
        $cpuResult = executeCommand('wmic cpu get loadpercentage');
        $memResult = executeCommand('wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /Value');
        $diskResult = executeCommand('wmic logicaldisk get size,freespace,caption');
        
        return [
            'cpu' => 'N/A (Windows)',
            'memory' => 'N/A (Windows)',
            'disk' => 'N/A (Windows)'
        ];
    } else {
        // Linux-Befehle
        // CPU: Verwende vmstat für zuverlässigere Werte
        $cpuResult = executeCommand('vmstat 1 2 | tail -1 | awk \'{print $13+$14"%"}\'');
        $cpu = trim($cpuResult['output']);
        
        // Fallback: Wenn vmstat nicht funktioniert, versuche mpstat
        if (empty($cpu) || !is_numeric(str_replace('%', '', $cpu))) {
            $cpuResult = executeCommand('grep "cpu " /proc/stat | awk \'{usage=($2+$4)*100/($2+$4+$5)} END {printf "%.1f%%", usage}\'');
            $cpu = trim($cpuResult['output']);
        }
        
        $memResult = executeCommand('free -h | awk \'/^Mem:/ {print $3 "/" $2}\'');
        $diskResult = executeCommand('df -h / | awk \'NR==2 {print $3 "/" $2 " (" $5 ")"}\'');
        
        return [
            'cpu' => $cpu ?: 'N/A',
            'memory' => trim($memResult['output']) ?: 'N/A',
            'disk' => trim($diskResult['output']) ?: 'N/A'
        ];
    }
}

// Logs sammeln
function getRecentLogs() {
    $logs = [];
    
    // SRS Logs
    $srsLogs = executeCommand('docker logs $(docker ps -q --filter "name=srs-vsgk4wcsoggo800w4ow48gos") --tail 10 2>&1');
    $logs[] = "=== SRS Logs ===\n" . $srsLogs['output'];
    
    // Auto-HLS Logs
    $hlsLogs = executeCommand('docker logs $(docker ps -q --filter "name=auto-hls-vsgk4wcsoggo800w4ow48gos") --tail 10 2>&1');
    $logs[] = "\n=== Auto-HLS Logs ===\n" . $hlsLogs['output'];
    
    return implode("\n", $logs);
}

// Sammle alle Diagnosedaten
$diagnoseData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'os' => PHP_OS,
    'php_version' => PHP_VERSION,
    'srs' => getSRSStreams(),
    'ffmpeg' => getFFmpegProcesses(),
    'containers' => getContainerStatus(),
    'system' => getSystemResources(),
    'logs' => getRecentLogs()
];

echo json_encode($diagnoseData, JSON_PRETTY_PRINT);
