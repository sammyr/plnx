<?php
/**
 * Debug-Seite: Prüft ob Poster-Bilder erstellt wurden
 */

$videoDir = __DIR__ . '/videos';
$isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Poster Check</title>";
echo "<style>
body { font-family: monospace; padding: 20px; background: #1a1a1a; color: #fff; }
.success { color: #4ade80; }
.error { color: #f87171; }
.warning { color: #fbbf24; }
table { border-collapse: collapse; width: 100%; margin: 20px 0; }
th, td { border: 1px solid #444; padding: 12px; text-align: left; }
th { background: #2a2a2a; }
img { max-width: 200px; border: 2px solid #444; border-radius: 4px; }
</style></head><body>";

echo "<h1>🎬 Poster-Generator Status</h1>";
echo "<p><strong>System:</strong> " . ($isWindows ? "Windows" : "Linux/Unix") . "</p>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>Video-Verzeichnis:</strong> $videoDir</p>";

// Prüfe FFmpeg (erweiterte Suche)
$ffmpeg = null;
$ffmpegPaths = [];

if ($isWindows) {
    exec('where ffmpeg 2>nul', $output, $code);
    if ($code === 0 && !empty($output[0])) {
        $ffmpeg = trim($output[0]);
    }
} else {
    // Versuche verschiedene Methoden
    exec('which ffmpeg 2>/dev/null', $output, $code);
    if ($code === 0 && !empty($output[0])) {
        $ffmpeg = trim($output[0]);
        $ffmpegPaths[] = "which: " . $ffmpeg;
    }
    
    // Prüfe bekannte Pfade
    $knownPaths = ['/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg', '/snap/bin/ffmpeg', '/opt/bin/ffmpeg'];
    foreach ($knownPaths as $path) {
        if (file_exists($path)) {
            if (!$ffmpeg) $ffmpeg = $path;
            $ffmpegPaths[] = "exists: " . $path;
        }
    }
    
    // Prüfe $PATH
    $pathEnv = getenv('PATH');
    $ffmpegPaths[] = "PATH: " . $pathEnv;
}

echo "<h2>🔍 FFmpeg Debug</h2>";
echo "<pre style='background: #2a2a2a; padding: 15px; border-radius: 4px;'>";
echo "Gefundene Pfade:\n";
foreach ($ffmpegPaths as $p) {
    echo "  " . htmlspecialchars($p) . "\n";
}
echo "\nPHP exec() erlaubt: " . (function_exists('exec') ? 'Ja' : 'Nein') . "\n";
echo "PHP shell_exec() erlaubt: " . (function_exists('shell_exec') ? 'Ja' : 'Nein') . "\n";

// Test exec
exec('ls /usr/bin/ffmpeg 2>&1', $testOutput, $testCode);
echo "\nTest: ls /usr/bin/ffmpeg\n";
echo "  Return Code: $testCode\n";
echo "  Output: " . implode("\n", $testOutput) . "\n";
echo "</pre>";

if ($ffmpeg) {
    echo "<p class='success'>✅ FFmpeg gefunden: $ffmpeg</p>";
    
    // FFmpeg Version
    exec("$ffmpeg -version 2>&1", $versionOutput);
    if (!empty($versionOutput[0])) {
        echo "<p class='success'>   " . htmlspecialchars($versionOutput[0]) . "</p>";
    }
} else {
    echo "<p class='error'>❌ FFmpeg NICHT gefunden!</p>";
    echo "<p class='warning'>⚠️  Installiere FFmpeg:</p>";
    if ($isWindows) {
        echo "<p>   Windows: <a href='https://ffmpeg.org/download.html' target='_blank'>ffmpeg.org/download.html</a></p>";
    } else {
        echo "<p>   Linux: <code>apt-get install ffmpeg</code> oder <code>yum install ffmpeg</code></p>";
    }
}

echo "<hr>";

// Prüfe Videos und Poster
$demoVideos = [
    ['title' => 'Die Totale Erinnerung', 'thumbnail' => 'ttr_preview.mp4'],
    ['title' => 'Horrible Boss', 'thumbnail' => 'Horrible-Boss.m4v'],
    ['title' => 'Stingray - Intro', 'thumbnail' => 'StingrayIntro.mp4']
];

echo "<h2>📹 Video & Poster Status</h2>";
echo "<table>";
echo "<tr><th>Video</th><th>Thumbnail</th><th>Poster</th><th>Vorschau</th></tr>";

foreach ($demoVideos as $video) {
    $thumbnailPath = "$videoDir/{$video['thumbnail']}";
    $nameWithoutExt = pathinfo($video['thumbnail'], PATHINFO_FILENAME);
    $posterPath = "$videoDir/{$nameWithoutExt}_poster.jpg";
    
    $thumbnailExists = file_exists($thumbnailPath);
    $posterExists = file_exists($posterPath);
    
    echo "<tr>";
    echo "<td><strong>" . htmlspecialchars($video['title']) . "</strong></td>";
    
    // Thumbnail Status
    if ($thumbnailExists) {
        $size = filesize($thumbnailPath);
        $sizeKB = round($size / 1024, 1);
        echo "<td class='success'>✅ {$video['thumbnail']}<br><small>({$sizeKB} KB)</small></td>";
    } else {
        echo "<td class='error'>❌ Nicht gefunden</td>";
    }
    
    // Poster Status
    if ($posterExists) {
        $size = filesize($posterPath);
        $sizeKB = round($size / 1024, 1);
        echo "<td class='success'>✅ {$nameWithoutExt}_poster.jpg<br><small>({$sizeKB} KB)</small></td>";
        echo "<td><img src='videos/{$nameWithoutExt}_poster.jpg' alt='Poster'></td>";
    } else {
        echo "<td class='error'>❌ Nicht erstellt</td>";
        echo "<td class='warning'>⏳ Warte auf Generierung...</td>";
    }
    
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<h2>📝 PHP Error Log (letzte 20 Zeilen)</h2>";
echo "<pre style='background: #2a2a2a; padding: 15px; border-radius: 4px; overflow-x: auto;'>";

// Zeige Error Log
$errorLog = ini_get('error_log');
if ($errorLog && file_exists($errorLog)) {
    $lines = file($errorLog);
    $lastLines = array_slice($lines, -20);
    foreach ($lastLines as $line) {
        if (strpos($line, 'Poster-Generator') !== false) {
            echo "<span class='warning'>" . htmlspecialchars($line) . "</span>";
        } else {
            echo htmlspecialchars($line);
        }
    }
} else {
    echo "Error Log nicht gefunden oder nicht konfiguriert.\n";
    echo "Prüfe: " . ($errorLog ?: 'nicht gesetzt') . "\n";
}

echo "</pre>";

echo "<hr>";
echo "<p><a href='viewer.php'>← Zurück zu viewer.php</a> | <a href='javascript:location.reload()'>🔄 Neu laden</a></p>";

echo "</body></html>";
