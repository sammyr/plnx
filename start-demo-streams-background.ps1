# Starte Demo-Video-Streams im Hintergrund (keine Fenster, ressourcenschonend)

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Demo-Streams im Hintergrund" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Prüfe ob FFmpeg verfügbar ist
if (-not (Get-Command ffmpeg -ErrorAction SilentlyContinue)) {
    Write-Host "FEHLER: FFmpeg nicht installiert!" -ForegroundColor Red
    Write-Host "Installation: choco install ffmpeg" -ForegroundColor Yellow
    exit 1
}

# Prüfe ob SRS läuft
try {
    $response = Invoke-WebRequest -Uri "http://localhost:1985/api/v1/versions" -UseBasicParsing -TimeoutSec 2
    Write-Host "SRS läuft: $(($response.Content | ConvertFrom-Json).data.version)" -ForegroundColor Green
} catch {
    Write-Host "WARNUNG: SRS scheint nicht zu laufen!" -ForegroundColor Yellow
    Write-Host "Starte mit: .\start.ps1" -ForegroundColor White
    Write-Host ""
}

# Video-Konfigurationen
$videos = @(
    @{File="html/videos/ttr.m4v"; Key="demo_video_stream"; Title="Die Totale Erinnerung"},
    @{File="html/videos/StingrayIntro.mp4"; Key="demo_stingray_stream"; Title="Stingray - Intro"},
    @{File="html/videos/Subway.m4v"; Key="demo_subway_stream"; Title="Subway"},
    @{File="html/videos/Chuck.und.Larry.m4v"; Key="demo_chuck_stream"; Title="Chuck und Larry"},
    @{File="html/videos/Horrible-Boss.m4v"; Key="demo_horrible_boss_stream"; Title="Horrible Boss"}
)

$logDir = "logs"
if (-not (Test-Path $logDir)) {
    New-Item -ItemType Directory -Path $logDir | Out-Null
}

$startedCount = 0

foreach ($video in $videos) {
    $fullPath = Join-Path (Get-Location) $video.File
    
    if (-not (Test-Path $fullPath)) {
        Write-Host "[SKIP] $($video.Title) - Datei nicht gefunden" -ForegroundColor Red
        continue
    }
    
    $logFile = Join-Path $logDir "$($video.Key).log"
    
    Write-Host "[START] $($video.Title)" -ForegroundColor Yellow
    Write-Host "  Stream: rtmp://localhost/live/$($video.Key)" -ForegroundColor Gray
    Write-Host "  Log: $logFile" -ForegroundColor Gray
    
    # FFmpeg-Befehl (ressourcenschonend)
    $ffmpegArgs = @(
        "-hide_banner",           # Weniger Output
        "-loglevel", "warning",   # Nur Warnungen loggen
        "-re",                    # Realtime (verhindert CPU-Spitzen)
        "-stream_loop", "-1",     # Endlos-Loop
        "-i", "`"$fullPath`"",    # Input-Datei
        "-c:v", "copy",           # Video NICHT neu encodieren (niedrige CPU!)
        "-c:a", "aac",            # Audio zu AAC (SRS benötigt AAC)
        "-b:a", "128k",           # Audio-Bitrate
        "-ar", "44100",           # Sample-Rate
        "-f", "flv",              # FLV-Format für RTMP
        "rtmp://localhost/live/$($video.Key)"
    )
    
    # Starte FFmpeg im Hintergrund (kein Fenster)
    $processInfo = New-Object System.Diagnostics.ProcessStartInfo
    $processInfo.FileName = "ffmpeg"
    $processInfo.Arguments = $ffmpegArgs -join " "
    $processInfo.RedirectStandardOutput = $true
    $processInfo.RedirectStandardError = $true
    $processInfo.UseShellExecute = $false
    $processInfo.CreateNoWindow = $true  # KEIN Fenster!
    
    $process = New-Object System.Diagnostics.Process
    $process.StartInfo = $processInfo
    
    # Log-Datei für Fehlersuche
    $process.add_ErrorDataReceived({
        param($sender, $e)
        if ($e.Data) {
            Add-Content -Path $using:logFile -Value $e.Data
        }
    })
    
    $process.Start() | Out-Null
    $process.BeginErrorReadLine()
    
    # Speichere PID für späteres Stoppen
    $process.Id | Out-File -FilePath "logs/$($video.Key).pid" -Force
    
    $startedCount++
    Start-Sleep -Milliseconds 500
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "  $startedCount Streams im Hintergrund!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Streams verfügbar:" -ForegroundColor Cyan
foreach ($video in $videos) {
    if (Test-Path (Join-Path (Get-Location) $video.File)) {
        Write-Host "  http://localhost:8080/watch.php?room=$($video.Key)" -ForegroundColor White
    }
}
Write-Host ""
Write-Host "Ressourcen-Verbrauch:" -ForegroundColor Cyan
Write-Host "  CPU: ~2-5% pro Stream (kein Re-Encoding!)" -ForegroundColor Gray
Write-Host "  RAM: ~50-80 MB pro Stream" -ForegroundColor Gray
Write-Host ""
Write-Host "Stoppen: .\stop-demo-streams.ps1" -ForegroundColor Yellow
Write-Host "Logs: logs/*.log" -ForegroundColor Gray
Write-Host ""
