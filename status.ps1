# SRS Streaming Server - Status-Skript

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  SRS Streaming Server - Status" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Container-Status
Write-Host "Container-Status:" -ForegroundColor Yellow
docker-compose ps
Write-Host ""

# Aktive Streams abfragen
Write-Host "Aktive Streams:" -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "http://localhost:1985/api/v1/streams/" -Method Get -ErrorAction Stop
    if ($response.streams) {
        Write-Host "  Anzahl aktiver Streams: $($response.streams.Count)" -ForegroundColor Green
        foreach ($stream in $response.streams) {
            Write-Host "  - Stream: $($stream.name)" -ForegroundColor Cyan
            Write-Host "    App: $($stream.app)" -ForegroundColor White
            Write-Host "    Clients: $($stream.clients)" -ForegroundColor White
        }
    } else {
        Write-Host "  Keine aktiven Streams" -ForegroundColor Yellow
    }
} catch {
    Write-Host "  ✗ Kann API nicht erreichen (Server läuft nicht?)" -ForegroundColor Red
}

Write-Host ""

# Server-Version
Write-Host "Server-Version:" -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "http://localhost:1985/api/v1/versions" -Method Get -ErrorAction Stop
    Write-Host "  SRS Version: $($response.data.major).$($response.data.minor).$($response.data.revision)" -ForegroundColor Green
} catch {
    Write-Host "  ✗ Kann API nicht erreichen" -ForegroundColor Red
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
