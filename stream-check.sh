#!/bin/bash

# Stream-Check: Prüft ob Demo-Videos als Streams laufen und startet sie bei Bedarf

VIDEO_DIR="/usr/local/srs/objs/nginx/html/videos"
RTMP_BASE="rtmp://localhost/live"
CHECK_INTERVAL=30  # Prüfe alle 30 Sekunden

# Video-Konfigurationen
declare -A VIDEOS
VIDEOS["demo_video_stream"]="ttr.m4v"
VIDEOS["demo_stingray_stream"]="StingrayIntro.mp4"
VIDEOS["demo_subway_stream"]="Subway.m4v"
VIDEOS["demo_chuck_stream"]="Chuck.und.Larry.m4v"

echo "[$(date)] Stream-Check gestartet"

# Funktion: Prüfe ob Stream aktiv ist
check_stream() {
    local stream_key=$1
    # Prüfe über SRS API
    curl -s "http://localhost:1985/api/v1/streams/" | grep -q "\"name\":\"$stream_key\""
    return $?
}

# Funktion: Starte Stream
start_stream() {
    local stream_key=$1
    local video_file=$2
    local video_path="$VIDEO_DIR/$video_file"
    
    if [ ! -f "$video_path" ]; then
        echo "[$(date)] SKIP: $stream_key - Video nicht gefunden: $video_path"
        return 1
    fi
    
    echo "[$(date)] START: $stream_key ($video_file)"
    
    # FFmpeg im Hintergrund starten (kein Re-Encoding!)
    nohup ffmpeg \
        -hide_banner \
        -loglevel error \
        -re \
        -stream_loop -1 \
        -i "$video_path" \
        -c:v copy \
        -c:a aac -b:a 128k -ar 44100 \
        -f flv "$RTMP_BASE/$stream_key" \
        > "/var/log/ffmpeg_${stream_key}.log" 2>&1 &
    
    echo $! > "/var/run/ffmpeg_${stream_key}.pid"
    echo "[$(date)] Stream gestartet: $stream_key (PID: $!)"
}

# Initiale Wartezeit für SRS-Start
echo "[$(date)] Warte auf SRS-Start..."
sleep 5

# Warte bis SRS RTMP-Port bereit ist
MAX_WAIT=60
WAITED=0
while ! nc -z localhost 1935; do
    if [ $WAITED -ge $MAX_WAIT ]; then
        echo "[$(date)] FEHLER: SRS RTMP-Port nicht erreichbar nach ${MAX_WAIT}s"
        exit 1
    fi
    echo "[$(date)] SRS noch nicht bereit, warte... (${WAITED}s/${MAX_WAIT}s)"
    sleep 2
    WAITED=$((WAITED + 2))
done

echo "[$(date)] SRS ist bereit! RTMP-Port 1935 erreichbar"
sleep 2  # Zusätzliche Sicherheit

# Hauptschleife
while true; do
    echo "[$(date)] Prüfe Streams..."
    
    for stream_key in "${!VIDEOS[@]}"; do
        video_file="${VIDEOS[$stream_key]}"
        
        if check_stream "$stream_key"; then
            echo "[$(date)] OK: $stream_key läuft"
        else
            echo "[$(date)] FEHLT: $stream_key - starte neu..."
            start_stream "$stream_key" "$video_file"
        fi
    done
    
    echo "[$(date)] Nächste Prüfung in $CHECK_INTERVAL Sekunden"
    sleep $CHECK_INTERVAL
done
