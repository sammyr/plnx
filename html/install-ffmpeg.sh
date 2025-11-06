#!/bin/bash
# Installiert FFmpeg im PHP-Container

echo "🎬 Installiere FFmpeg im Container..."

# Prüfe welcher Package Manager verfügbar ist
if command -v apk &> /dev/null; then
    # Alpine Linux (php:*-alpine)
    echo "📦 Alpine Linux erkannt, verwende apk..."
    apk update
    apk add ffmpeg
elif command -v apt-get &> /dev/null; then
    # Debian/Ubuntu
    echo "📦 Debian/Ubuntu erkannt, verwende apt..."
    apt-get update
    apt-get install -y ffmpeg
elif command -v yum &> /dev/null; then
    # CentOS/RHEL
    echo "📦 CentOS/RHEL erkannt, verwende yum..."
    yum install -y ffmpeg
else
    echo "❌ Kein bekannter Package Manager gefunden!"
    exit 1
fi

# Prüfe Installation
if command -v ffmpeg &> /dev/null; then
    echo "✅ FFmpeg erfolgreich installiert!"
    ffmpeg -version | head -n 1
else
    echo "❌ FFmpeg Installation fehlgeschlagen!"
    exit 1
fi
