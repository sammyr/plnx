#!/bin/bash
# Stoppe alle Demo-Video-Streams

echo "Stoppe Demo-Streams..."

PID_FILE="demo-streams.pids"

if [ -f "$PID_FILE" ]; then
    while read pid; do
        if ps -p $pid > /dev/null 2>&1; then
            echo "Stoppe Prozess $pid"
            kill $pid
        fi
    done < "$PID_FILE"
    rm -f "$PID_FILE"
    echo "Alle Streams gestoppt!"
else
    echo "Keine laufenden Streams gefunden."
    # Fallback: Suche alle FFmpeg-Prozesse mit "rtmp://localhost/live"
    pkill -f "ffmpeg.*rtmp://localhost/live"
fi
