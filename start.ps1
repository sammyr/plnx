# SRS Streaming Server - Start-Skript
# Dieses Skript startet den SRS-Server und öffnet den Browser

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Streaming Server - Start" -ForegroundColor Cyan
Write-Host "  (SRS + WebRTC Signaling)" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Prüfe ob Docker läuft
Write-Host "[1/4] Prüfe Docker-Status..." -ForegroundColor Yellow
$dockerRunning = $false
try {
    docker ps 2>&1 | Out-Null
    if ($LASTEXITCODE -eq 0) {
        $dockerRunning = $true
        Write-Host "  [OK] Docker laeuft" -ForegroundColor Green
    }
} catch {
    $dockerRunning = $false
}

if (-not $dockerRunning) {
    Write-Host "  [FEHLER] Docker laeuft nicht!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Bitte starte Docker Desktop und führe dieses Skript erneut aus." -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Docker Desktop starten? (J/N): " -NoNewline -ForegroundColor Cyan
    $response = Read-Host
    if ($response -eq "J" -or $response -eq "j") {
        Write-Host "Starte Docker Desktop..." -ForegroundColor Yellow
        Start-Process "C:\Program Files\Docker\Docker\Docker Desktop.exe"
        Write-Host "Warte 30 Sekunden bis Docker gestartet ist..." -ForegroundColor Yellow
        Start-Sleep -Seconds 30
    } else {
        Write-Host "Abgebrochen." -ForegroundColor Red
        exit 1
    }
}

Write-Host ""

# Starte Docker-Container
Write-Host "[2/4] Starte SRS-Container..." -ForegroundColor Yellow
docker-compose up -d

if ($LASTEXITCODE -eq 0) {
    Write-Host "  [OK] Container gestartet" -ForegroundColor Green
} else {
    Write-Host "  [FEHLER] Fehler beim Starten" -ForegroundColor Red
    exit 1
}

Write-Host ""

# Warte kurz
Write-Host "[3/4] Warte auf Server-Start..." -ForegroundColor Yellow
Start-Sleep -Seconds 3
Write-Host "  [OK] Server bereit" -ForegroundColor Green

Write-Host ""

# Preview-Generator automatisch starten (asynchron)
# DEAKTIVIERT - Nutze Screenshot-System in stream.html statt FLV-Generator
# try {
#     $previewScript = Join-Path $PSScriptRoot "tools\preview-generator.js"
#     if (Test-Path $previewScript) {
#         Write-Host "[Auto] Starte Preview-Generator..." -ForegroundColor Yellow
#         Start-Process node -ArgumentList "`"$previewScript`"" -WindowStyle Hidden
#         Write-Host "  [OK] Preview-Generator gestartet" -ForegroundColor Green
#     } else {
#         Write-Host "[Auto] Preview-Generator nicht gefunden: $previewScript" -ForegroundColor DarkYellow
#     }
# } catch {
#     Write-Host "[Auto] Konnte Preview-Generator nicht starten: $($_.Exception.Message)" -ForegroundColor DarkYellow
# }

# RTMP-Preview Publisher automatisch starten (optional, Demo)
# DEAKTIVIERT - Nutze echten WebRTC-Broadcaster statt Test-Publisher
# try {
#     $rtmpPreview = Join-Path $PSScriptRoot "tools\start-rtmp-preview.ps1"
#     if (Test-Path $rtmpPreview) {
#         Write-Host "[Auto] Starte RTMP-Preview Publisher (Driver-Berlin-001)..." -ForegroundColor Yellow
#         Start-Process powershell -ArgumentList "-ExecutionPolicy Bypass -File `"$rtmpPreview`" -RoomId `"Driver-Berlin-001`"" -WindowStyle Hidden
#         Write-Host "  [OK] RTMP-Preview Publisher gestartet" -ForegroundColor Green
#     }
# } catch {
#     Write-Host "[Auto] Konnte RTMP-Preview Publisher nicht starten: $($_.Exception.Message)" -ForegroundColor DarkYellow
# }

# Zeige Informationen
Write-Host "[4/4] Server-Informationen:" -ForegroundColor Yellow
Write-Host ""
Write-Host "  WebRTC (Echtzeit - EMPFOHLEN!):" -ForegroundColor Green
Write-Host "     Broadcaster: http://localhost:8080/stream.html" -ForegroundColor White
Write-Host "     Viewer:      http://localhost:8080/viewer.html" -ForegroundColor White
Write-Host ""
Write-Host "  RTMP Publish URL (fuer OBS):" -ForegroundColor Cyan
Write-Host "     rtmp://localhost:1935/live/livestream" -ForegroundColor White
Write-Host ""
Write-Host "  Web Player:" -ForegroundColor Cyan
Write-Host "     http://localhost:8080/index.html" -ForegroundColor White
Write-Host ""
Write-Host "  HTTP API:" -ForegroundColor Cyan
Write-Host "     http://localhost:1985/api/v1/" -ForegroundColor White
Write-Host ""
Write-Host "  Signaling Server:" -ForegroundColor Cyan
Write-Host "     http://localhost:3000/health" -ForegroundColor White
Write-Host ""

# Öffne Browser
Write-Host "Welche Seite öffnen?" -ForegroundColor Cyan
Write-Host "  [1] WebRTC Broadcaster (stream.html)" -ForegroundColor White
Write-Host "  [2] WebRTC Viewer (viewer.html)" -ForegroundColor White
Write-Host "  [3] HTML Player (index.html)" -ForegroundColor White
Write-Host "  [N] Keine" -ForegroundColor White
Write-Host ""
Write-Host "Auswahl (1/2/3/N): " -NoNewline -ForegroundColor Cyan
$response = Read-Host

switch ($response) {
    "1" {
        Start-Process "http://localhost:8080/stream.html"
        Write-Host "  [OK] WebRTC Broadcaster geoeffnet" -ForegroundColor Green
    }
    "2" {
        Start-Process "http://localhost:8080/viewer.html"
        Write-Host "  [OK] WebRTC Viewer geoeffnet" -ForegroundColor Green
    }
    "3" {
        Start-Process "http://localhost:8080/index.html"
        Write-Host "  [OK] HTML Player geoeffnet" -ForegroundColor Green
    }
    default {
        Write-Host "  [SKIP] Uebersprungen" -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Server laeuft! " -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Zum Stoppen: docker-compose stop" -ForegroundColor Yellow
Write-Host "Logs anzeigen: docker-compose logs -f" -ForegroundColor Yellow
Write-Host ""
