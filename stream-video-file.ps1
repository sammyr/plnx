# Stream Video-Datei über RTMP zu SRS
# Verwendung: .\stream-video-file.ps1 -VideoFile "videos/StingrayIntro.mp4" -StreamKey "demo_stingray_stream"

param(
    [Parameter(Mandatory=$true)]
    [string]$VideoFile,
    
    [Parameter(Mandatory=$true)]
    [string]$StreamKey,
    
    [string]$RtmpUrl = "rtmp://localhost/live"
)

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Video-Datei zu RTMP Stream" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Video: $VideoFile" -ForegroundColor Yellow
Write-Host "Stream: $RtmpUrl/$StreamKey" -ForegroundColor Yellow
Write-Host ""

# Prüfe ob FFmpeg verfügbar ist
if (-not (Get-Command ffmpeg -ErrorAction SilentlyContinue)) {
    Write-Host "FEHLER: FFmpeg nicht gefunden!" -ForegroundColor Red
    Write-Host "Installiere FFmpeg: https://ffmpeg.org/download.html" -ForegroundColor Yellow
    exit 1
}

# Prüfe ob Video existiert
$fullPath = Join-Path (Get-Location) $VideoFile
if (-not (Test-Path $fullPath)) {
    Write-Host "FEHLER: Video nicht gefunden: $fullPath" -ForegroundColor Red
    exit 1
}

Write-Host "Starte Stream..." -ForegroundColor Green
Write-Host "Drücke Strg+C zum Beenden" -ForegroundColor Yellow
Write-Host ""

# FFmpeg: Video-Datei als Loop-Stream
# -re: Realtime-Geschwindigkeit
# -stream_loop -1: Endlos-Loop
# -c copy: Kein Re-Encoding (niedrige CPU-Last)
# -f flv: FLV-Format für RTMP
ffmpeg -re -stream_loop -1 -i "$fullPath" `
    -c:v copy `
    -c:a aac -b:a 128k `
    -f flv "$RtmpUrl/$StreamKey"

Write-Host ""
Write-Host "Stream beendet." -ForegroundColor Yellow
