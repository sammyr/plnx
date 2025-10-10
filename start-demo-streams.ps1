# Starte alle Demo-Video-Streams automatisch

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Demo-Video-Streams starten" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Prüfe ob FFmpeg verfügbar ist
if (-not (Get-Command ffmpeg -ErrorAction SilentlyContinue)) {
    Write-Host "FEHLER: FFmpeg nicht installiert!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Installation:" -ForegroundColor Yellow
    Write-Host "  choco install ffmpeg" -ForegroundColor White
    Write-Host "  ODER von https://ffmpeg.org/download.html" -ForegroundColor White
    Write-Host ""
    exit 1
}

Write-Host "FFmpeg gefunden: $(ffmpeg -version | Select-String 'ffmpeg version' | Select-Object -First 1)" -ForegroundColor Green
Write-Host ""

# Array mit Video-Konfigurationen
$videos = @(
    @{File="html/videos/ttr.m4v"; Key="demo_video_stream"; Title="Die Totale Erinnerung"},
    @{File="html/videos/StingrayIntro.mp4"; Key="demo_stingray_stream"; Title="Stingray - Intro"},
    @{File="html/videos/Subway.m4v"; Key="demo_subway_stream"; Title="Subway"},
    @{File="html/videos/Chuck.und.Larry.m4v"; Key="demo_chuck_stream"; Title="Chuck und Larry"}
)

$processes = @()

foreach ($video in $videos) {
    $fullPath = Join-Path (Get-Location) $video.File
    
    if (Test-Path $fullPath) {
        Write-Host "[START] $($video.Title)" -ForegroundColor Yellow
        Write-Host "  Stream: rtmp://localhost/live/$($video.Key)" -ForegroundColor Gray
        Write-Host "  HLS: http://localhost:8080/live/$($video.Key).m3u8" -ForegroundColor Gray
        
        # Starte FFmpeg in neuem Fenster
        $process = Start-Process powershell -ArgumentList @(
            "-NoExit",
            "-Command",
            "Write-Host 'Streaming: $($video.Title)' -ForegroundColor Cyan; " +
            "ffmpeg -re -stream_loop -1 -i '$fullPath' -c:v copy -c:a aac -b:a 128k -f flv 'rtmp://localhost/live/$($video.Key)'"
        ) -PassThru
        
        $processes += $process
        Start-Sleep -Seconds 1
    } else {
        Write-Host "[SKIP] $($video.Title) - Datei nicht gefunden: $fullPath" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "  $($processes.Count) Streams gestartet!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Streams verfügbar unter:" -ForegroundColor Cyan
foreach ($video in $videos) {
    Write-Host "  http://localhost:8080/watch.php?room=$($video.Key)" -ForegroundColor White
}
Write-Host ""
Write-Host "Zum Beenden: Schließe die FFmpeg-Fenster" -ForegroundColor Yellow
Write-Host ""
