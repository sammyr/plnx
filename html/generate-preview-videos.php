<?php
/**
 * Automatische Vorschau-Video-Generierung
 * Erstellt 10-Sekunden-Clips aus den Hauptvideos
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
        die("❌ FFmpeg nicht gefunden. Bitte installieren: apk add ffmpeg\n");
    }
}

echo "🎬 Vorschau-Video-Generator gestartet\n";
echo "📁 Video-Verzeichnis: $videoDir\n";
echo "🔧 FFmpeg: $ffmpegPath\n\n";

// Video-Konfiguration
$videos = [
    ['source' => 'ttr.m4v', 'preview' => 'ttr_preview.mp4'],
    ['source' => 'Horrible-Boss.m4v', 'preview' => 'Horrible-Boss_preview.mp4'],
    ['source' => 'StingrayIntro.mp4', 'preview' => 'StingrayIntro_preview.mp4']
];

$generated = 0;
$skipped = 0;
$errors = 0;

foreach ($videos as $video) {
    $sourcePath = "$videoDir/{$video['source']}";
    $previewPath = "$videoDir/{$video['preview']}";
    
    // Überspringe wenn Vorschau bereits existiert
    if (file_exists($previewPath)) {
        echo "⏭️  Überspringe {$video['source']} (Vorschau existiert bereits)\n";
        $skipped++;
        continue;
    }
    
    // Prüfe ob Quellvideo existiert
    if (!file_exists($sourcePath)) {
        echo "❌ Quellvideo nicht gefunden: {$video['source']}\n";
        $errors++;
        continue;
    }
    
    echo "🎨 Generiere 10s Vorschau für: {$video['source']}\n";
    
    // Generiere 10-Sekunden-Clip ab Sekunde 3
    // -ss 3: Start bei Sekunde 3
    // -t 10: Dauer 10 Sekunden
    // -vf scale=854:-1: Skaliere auf 854px Breite (480p)
    // -c:v libx264: H.264 Codec
    // -preset fast: Schnelle Kodierung
    // -crf 28: Qualität (18-28, niedriger = besser)
    // -c:a aac: AAC Audio
    // -b:a 96k: Audio Bitrate
    $command = sprintf(
        '%s -ss 3 -i %s -t 10 -vf "scale=854:-1" -c:v libx264 -preset fast -crf 28 -c:a aac -b:a 96k -movflags +faststart %s 2>&1',
        escapeshellarg($ffmpegPath),
        escapeshellarg($sourcePath),
        escapeshellarg($previewPath)
    );
    
    $output = [];
    $returnCode = 0;
    exec($command, $output, $returnCode);
    
    if ($returnCode === 0 && file_exists($previewPath)) {
        $filesize = filesize($previewPath);
        $filesizeKB = round($filesize / 1024, 1);
        echo "   ✅ Vorschau erstellt: {$video['preview']} ({$filesizeKB} KB)\n";
        $generated++;
    } else {
        echo "   ❌ Fehler beim Erstellen der Vorschau\n";
        echo "   Befehl: $command\n";
        echo "   Output: " . implode("\n", $output) . "\n";
        $errors++;
    }
    
    echo "\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📊 Zusammenfassung:\n";
echo "   ✅ Generiert: $generated\n";
echo "   ⏭️  Übersprungen: $skipped\n";
echo "   ❌ Fehler: $errors\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

if ($generated > 0) {
    echo "\n✨ Fertig! Die Vorschau-Videos wurden erstellt.\n";
}
