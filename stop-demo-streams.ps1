# Stoppe alle FFmpeg-Prozesse (Demo-Streams)

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Demo-Streams stoppen" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$stoppedCount = 0

# Methode 1: Stoppe über PID-Dateien (sauberer)
if (Test-Path "logs/*.pid") {
    $pidFiles = Get-ChildItem -Path "logs" -Filter "*.pid"
    
    foreach ($pidFile in $pidFiles) {
        $processId = Get-Content $pidFile.FullName
        try {
            $process = Get-Process -Id $processId -ErrorAction Stop
            Write-Host "Stoppe Stream: $($pidFile.BaseName) (PID: $processId)" -ForegroundColor Yellow
            Stop-Process -Id $processId -Force
            Remove-Item $pidFile.FullName -Force
            $stoppedCount++
        } catch {
            Write-Host "Prozess $pid nicht mehr aktiv" -ForegroundColor Gray
            Remove-Item $pidFile.FullName -Force
        }
    }
}

# Methode 2: Stoppe alle verbleibenden FFmpeg-Prozesse
$ffmpegProcesses = Get-Process -Name ffmpeg -ErrorAction SilentlyContinue

if ($ffmpegProcesses) {
    Write-Host ""
    Write-Host "Stoppe verbleibende FFmpeg-Prozesse..." -ForegroundColor Yellow
    
    foreach ($process in $ffmpegProcesses) {
        Write-Host "  Prozess: $($process.Id)" -ForegroundColor Gray
        Stop-Process -Id $process.Id -Force
        $stoppedCount++
    }
}

Write-Host ""
if ($stoppedCount -gt 0) {
    Write-Host "$stoppedCount Stream(s) gestoppt!" -ForegroundColor Green
} else {
    Write-Host "Keine laufenden Streams gefunden." -ForegroundColor Yellow
}
Write-Host ""
