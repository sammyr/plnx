#!/bin/sh
# Auto-start HLS streams for all videos

echo "🎬 Starting HLS streams..."

# Wait for SRS to be ready
sleep 5

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
ps | grep ffmpeg | grep -v grep

# Keep container running - wait for all background jobs
echo "⏳ Keeping container alive..."
wait

# If wait exits (shouldn't happen), fall back to infinite loop
while true; do
    sleep 3600
done
