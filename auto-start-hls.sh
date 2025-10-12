#!/bin/sh
# Auto-start HLS streams for all videos

echo "🎬 Starting HLS streams..."

# Mapping: video file -> room ID
start_stream() {
    FILE="$1"
    ROOM="$2"
    echo "▶️  Starting: $ROOM ($FILE)"
    
    # Start FFmpeg in background
    ffmpeg -re -stream_loop -1 -i "/videos/$FILE" \
        -c copy -f flv "rtmp://srs:1935/live/$ROOM" \
        > /dev/null 2>&1 &
    
    echo "   PID: $!"
}

# Start all streams
start_stream "Subway.m4v" "demo_video_stream"
start_stream "Horrible-Boss.m4v" "demo_horrible_boss_stream"
start_stream "Chuck.und.Larry.m4v" "demo_chuck_larry_stream"
start_stream "ttr.m4v" "demo_ttr_stream"

echo "✅ All HLS streams started"
echo "📊 Active processes:"
ps aux | grep ffmpeg | grep -v grep

# Keep container running
tail -f /dev/null
