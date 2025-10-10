#!/bin/bash
# Starte alle Demo-Video-Streams automatisch

echo "========================================"
echo "  Demo-Video-Streams starten"
echo "========================================"
echo ""

# Prüfe ob FFmpeg verfügbar ist
if ! command -v ffmpeg &> /dev/null; then
    echo "FEHLER: FFmpeg nicht installiert!"
    echo ""
    echo "Installation:"
    echo "  sudo apt-get install ffmpeg"
    echo ""
    exit 1
fi

echo "FFmpeg gefunden: $(ffmpeg -version | head -n1)"
echo ""

# Array mit Video-Konfigurationen
declare -a videos=(
    "/videos/ttr.m4v|demo_video_stream|Die Totale Erinnerung"
    "/videos/StingrayIntro.mp4|demo_stingray_stream|Stingray - Intro"
    "/videos/Subway.m4v|demo_subway_stream|Subway"
    "/videos/Chuck.und.Larry.m4v|demo_chuck_stream|Chuck und Larry"
)

# PID-Datei zum Tracking
PID_FILE="demo-streams.pids"
rm -f "$PID_FILE"

count=0
for video_config in "${videos[@]}"; do
    IFS='|' read -r file key title <<< "$video_config"
    
    if [ -f "$file" ]; then
        echo "[START] $title"
        echo "  Stream: rtmp://srs:1935/live/$key"
        echo "  HLS: http://localhost:8081/live/$key.m3u8"
        
        # Starte FFmpeg im Hintergrund
        nohup ffmpeg -re -stream_loop -1 -i "$file" \
            -c:v copy \
            -c:a aac -b:a 128k \
            -f flv "rtmp://srs:1935/live/$key" \
            > "/logs/stream-$key.log" 2>&1 &
        
        # Speichere PID
        echo $! >> "$PID_FILE"
        ((count++))
        sleep 1
    else
        echo "[SKIP] $title - Datei nicht gefunden: $file"
    fi
done

echo ""
echo "========================================"
echo "  $count Streams gestartet!"
echo "========================================"
echo ""
echo "Streams verfügbar unter:"
for video_config in "${videos[@]}"; do
    IFS='|' read -r file key title <<< "$video_config"
    echo "  http://localhost:8080/watch-movie.php?room=$key"
done
echo ""
echo "Zum Beenden: ./stop-demo-streams.sh"
echo "Logs: /logs/stream-*.log"
echo ""
echo "Streams laufen im Hintergrund..."

# Halte Container am Leben
tail -f /dev/null
