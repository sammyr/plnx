<?php
header('Content-Type: application/json');

// Nur POST erlauben
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Hilfsfunktion zum Ausführen von Docker-Befehlen
function executeDockerCommand($args) {
    // Docker läuft im Container - verwende docker exec vom Host
    // Der PHP-Container kann mit dem Docker-Socket kommunizieren
    
    // Prüfe ob Docker-Socket verfügbar ist
    if (file_exists('/var/run/docker.sock')) {
        // Verwende docker direkt über Socket
        $command = 'docker ' . $args . ' 2>&1';
    } else {
        // Fallback: Versuche lokales Docker
        $dockerPaths = ['/usr/bin/docker', '/usr/local/bin/docker'];
        $docker = 'docker';
        foreach ($dockerPaths as $path) {
            if (file_exists($path)) {
                $docker = $path;
                break;
            }
        }
        $command = $docker . ' ' . $args . ' 2>&1';
    }
    
    $output = '';
    $returnCode = 0;
    exec($command, $outputArray, $returnCode);
    $output = implode("\n", $outputArray);
    
    return [
        'output' => $output, 
        'code' => $returnCode,
        'command' => $command,
        'docker_socket' => file_exists('/var/run/docker.sock') ? 'yes' : 'no'
    ];
}

try {
    $projectId = 'vsgk4wcsoggo800w4ow48gos';
    
    // Schritt 1: Finde Container-ID für auto-hls mit Projekt-ID
    // Container-Name: auto-hls-vsgk4wcsoggo800w4ow48gos-TIMESTAMP
    $findResult = executeDockerCommand('ps -q --filter "name=auto-hls-' . $projectId . '"');
    $findOutput = array_filter(explode("\n", trim($findResult['output'])));
    
    if ($findResult['code'] !== 0 || empty($findOutput)) {
        // Fallback: Suche nach allen Containern mit Projekt-ID
        $allResult = executeDockerCommand('ps -aq');
        $allContainers = array_filter(explode("\n", trim($allResult['output'])));
        
        $containerIds = [];
        foreach ($allContainers as $cid) {
            if (empty($cid)) continue;
            
            // Prüfe Container-Name
            $nameResult = executeDockerCommand('inspect --format="{{.Name}}" ' . escapeshellarg($cid));
            
            if ($nameResult['code'] === 0 && !empty($nameResult['output'])) {
                $containerName = trim($nameResult['output'], '/');
                if (strpos($containerName, 'auto-hls') !== false || strpos($containerName, $projectId) !== false) {
                    $containerIds[] = $cid;
                }
            }
        }
        
        if (empty($containerIds)) {
            // Debug: Zeige alle Container-Namen
            $allNames = [];
            foreach ($allContainers as $cid) {
                if (empty($cid)) continue;
                $nameResult = executeDockerCommand('inspect --format="{{.Name}}" ' . escapeshellarg($cid));
                if ($nameResult['code'] === 0 && !empty($nameResult['output'])) {
                    $allNames[] = trim($nameResult['output'], '/');
                }
            }
            
            echo json_encode([
                'success' => false,
                'error' => 'No auto-hls container found. Docker is not installed on this system. Please install Docker or mount the Docker socket.',
                'debug' => [
                    'docker_socket' => $findResult['docker_socket'] ?? 'no',
                    'find_command' => $findResult['command'] ?? 'unknown',
                    'find_output' => $findOutput,
                    'all_containers_count' => count($allContainers),
                    'all_container_names' => $allNames,
                    'searching_for' => 'auto-hls-' . $projectId,
                    'solution' => 'Mount Docker socket: -v /var/run/docker.sock:/var/run/docker.sock or install Docker in container'
                ]
            ]);
            exit;
        }
        
        $containerId = $containerIds[0];
    } else {
        $containerId = trim($findOutput[0]);
    }
    
    // Schritt 2: Restart Container
    $restartResult = executeDockerCommand('restart ' . escapeshellarg($containerId));
    
    if ($restartResult['code'] === 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Streams werden neu gestartet',
            'container_id' => $containerId,
            'output' => $restartResult['output']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Container konnte nicht neu gestartet werden',
            'container_id' => $containerId,
            'command' => $restartResult['command'],
            'output' => $restartResult['output'],
            'return_code' => $restartResult['code']
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
