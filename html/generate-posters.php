<?php
/**
 * Automatische Poster-Generierung für Video-Thumbnails
 * Erstellt Standbilder bei Sekunde 10 aus Videos
 */

// Konfiguration
$videoDir = __DIR__ . '/videos';
$ffmpegPath = '/usr/bin/ffmpeg'; // Pfad zu FFmpeg

// Prüfe ob FFmpeg verfügbar ist
if (!file_exists($ffmpegPath)) {
    // Versuche FFmpeg im PATH zu finden
    exec('which ffmpeg 2>/dev/null', $output, $code);
    if ($code === 0 && !empty($output[0])) {
        $ffmpegPath = trim($output[0]);
    } else {
        die("❌ FFmpeg nicht gefunden. Bitte installieren: apt-get install ffmpeg\n");
    }
}

echo "🎬 Poster-Generator gestartet\n";
echo "📁 Video-Verzeichnis: $videoDir\n";
echo "🔧 FFmpeg: $ffmpegPath\n\n";

// Finde alle Video-Dateien
$videoExtensions = ['mp4', 'm4v', 'mkv', 'avi', 'mov'];
$videos = [];

foreach ($videoExtensions as $ext) {
    $files = glob("$videoDir/*.$ext");
    if ($files) {
        $videos = array_merge($videos, $files);
    }
}

if (empty($videos)) {
    echo "⚠️  Keine Videos gefunden in $videoDir\n";
    exit(0);
}

echo "📹 " . count($videos) . " Videos gefunden\n\n";

$generated = 0;
$skipped = 0;
$errors = 0;

foreach ($videos as $videoPath) {
    $filename = basename($videoPath);
    $nameWithoutExt = pathinfo($videoPath, PATHINFO_FILENAME);
    $posterPath = "$videoDir/{$nameWithoutExt}_poster.jpg";
    
    // Überspringe wenn Poster bereits existiert
    if (file_exists($posterPath)) {
        echo "⏭️  Überspringe $filename (Poster existiert bereits)\n";
        $skipped++;
        continue;
    }
    
    echo "🎨 Generiere Poster für: $filename\n";
    
    // Prüfe Video-Länge
    $durationCmd = escapeshellarg($ffmpegPath) . " -i " . escapeshellarg($videoPath) . " 2>&1 | grep Duration";
    exec($durationCmd, $durationOutput);
    
    $timestamp = '00:00:10'; // Standard: 10 Sekunden
    
    if (!empty($durationOutput[0]) && preg_match('/Duration: (\d{2}):(\d{2}):(\d{2})/', $durationOutput[0], $matches)) {
        $hours = (int)$matches[1];
        $minutes = (int)$matches[2];
        $seconds = (int)$matches[3];
        $totalSeconds = $hours * 3600 + $minutes * 60 + $seconds;
        
        // Wenn Video kürzer als 10 Sekunden, nehme Mitte
        if ($totalSeconds < 10) {
            $timestamp = sprintf('00:00:%02d', floor($totalSeconds / 2));
            echo "   ℹ️  Video kürzer als 10s, verwende Mitte: $timestamp\n";
        }
    }
    
    // Generiere Poster mit FFmpeg (mit -update Option für einzelnes Bild)
    $command = sprintf(
        '%s -ss %s -i %s -vframes 1 -update 1 -q:v 2 -vf "scale=1280:-1" %s 2>&1',
        escapeshellarg($ffmpegPath),
        escapeshellarg($timestamp),
        escapeshellarg($videoPath),
        escapeshellarg($posterPath)
    );
    
    exec($command, $output, $returnCode);
    
    if ($returnCode === 0 && file_exists($posterPath)) {
        $filesize = filesize($posterPath);
        $filesizeKB = round($filesize / 1024, 1);
        echo "   ✅ Poster erstellt: {$nameWithoutExt}_poster.jpg ({$filesizeKB} KB)\n";
        $generated++;
    } else {
        echo "   ❌ Fehler beim Erstellen des Posters\n";
        echo "   Befehl: $command\n";
        echo "   Output: " . implode("\n", $output) . "\n";
        $errors++;
    }
    
    echo "\n";
    
    // Cleanup
    $output = [];
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📊 Zusammenfassung:\n";
echo "   ✅ Generiert: $generated\n";
echo "   ⏭️  Übersprungen: $skipped\n";
echo "   ❌ Fehler: $errors\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

if ($generated > 0) {
    echo "\n✨ Fertig! Die Poster-Bilder wurden erstellt.\n";
}
