#!/bin/bash
# Erstelle Poster-Bilder direkt auf dem Server
# Führe dieses Script auf dem Server aus: bash create-posters-on-server.sh

echo "🎬 Erstelle Poster-Bilder für Video-Thumbnails..."
echo ""

# Finde das Videos-Verzeichnis
VIDEO_DIR="/var/www/html/videos"

if [ ! -d "$VIDEO_DIR" ]; then
    echo "❌ Video-Verzeichnis nicht gefunden: $VIDEO_DIR"
    echo "Suche nach alternativen Pfaden..."
    
    # Suche in Docker Volumes
    VIDEO_DIR=$(docker exec php-fpm-vsgk4wcsoggo800w4ow48gos-220528709828 pwd 2>/dev/null)/videos
    
    if [ ! -d "$VIDEO_DIR" ]; then
        echo "❌ Konnte Video-Verzeichnis nicht finden!"
        exit 1
    fi
fi

echo "📁 Video-Verzeichnis: $VIDEO_DIR"
cd "$VIDEO_DIR" || exit 1

# Prüfe ob FFmpeg verfügbar ist
if ! command -v ffmpeg &> /dev/null; then
    echo "❌ FFmpeg nicht gefunden. Installiere FFmpeg:"
    echo "   apt-get update && apt-get install -y ffmpeg"
    exit 1
fi

echo "✅ FFmpeg gefunden: $(ffmpeg -version | head -n1)"
echo ""

# Erstelle Poster für jedes Video
videos=(
    "ttr.m4v:ttr_poster.jpg"
    "Horrible-Boss.m4v:Horrible-Boss_poster.jpg"
    "StingrayIntro.mp4:StingrayIntro_poster.jpg"
)

for video_pair in "${videos[@]}"; do
    IFS=':' read -r video poster <<< "$video_pair"
    
    if [ -f "$video" ]; then
        if [ -f "$poster" ]; then
            echo "⏭️  Überspringe $poster (existiert bereits)"
        else
            echo "🎨 Erstelle $poster aus $video..."
            ffmpeg -ss 00:00:10 -i "$video" -vframes 1 -update 1 -q:v 2 -vf "scale=1280:-1" "$poster" -y 2>&1 | grep -E "(frame=|error|Error)"
            
            if [ -f "$poster" ]; then
                echo "   ✅ Erfolgreich erstellt: $poster ($(du -h "$poster" | cut -f1))"
                chmod 644 "$poster"
            else
                echo "   ❌ Fehler beim Erstellen von $poster"
            fi
        fi
    else
        echo "❌ Video nicht gefunden: $video"
    fi
    echo ""
done

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📊 Ergebnis:"
ls -lh *_poster.jpg 2>/dev/null || echo "Keine Poster-Bilder gefunden"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "✨ Fertig! Die Poster-Bilder wurden erstellt."
echo "🌐 Teste die Seite: https://stream.sammyrichter.de"
