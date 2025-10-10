# SRS mit PHP neu bauen und starten

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  SRS + PHP-FPM Setup" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Stoppe alte Container
Write-Host "[1/3] Stoppe alte Container..." -ForegroundColor Yellow
docker-compose down

Write-Host ""

# Baue neues Image mit PHP
Write-Host "[2/3] Baue SRS-Image mit PHP-Support..." -ForegroundColor Yellow
docker-compose build --no-cache srs

Write-Host ""

# Starte alle Services
Write-Host "[3/3] Starte Services..." -ForegroundColor Yellow
docker-compose up -d

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "  Setup abgeschlossen!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Warte 15 Sekunden auf Container-Start..." -ForegroundColor Yellow
Start-Sleep -Seconds 15

Write-Host ""
Write-Host "Container-Status:" -ForegroundColor Cyan
docker-compose ps

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Services verfügbar:" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "PHP-Test:" -ForegroundColor Yellow
Write-Host "  http://localhost:8080/test-php.php" -ForegroundColor White
Write-Host ""
Write-Host "Viewer (mit automatischen Video-Streams):" -ForegroundColor Yellow
Write-Host "  http://localhost:8080/viewer.php" -ForegroundColor White
Write-Host ""
Write-Host "Demo-Videos (werden automatisch gestreamt):" -ForegroundColor Yellow
Write-Host "  http://localhost:8080/watch.php?room=demo_video_stream" -ForegroundColor White
Write-Host "  http://localhost:8080/watch.php?room=demo_stingray_stream" -ForegroundColor White
Write-Host "  http://localhost:8080/watch.php?room=demo_subway_stream" -ForegroundColor White
Write-Host "  http://localhost:8080/watch.php?room=demo_chuck_stream" -ForegroundColor White
Write-Host ""
Write-Host "Stream-Status prüfen:" -ForegroundColor Yellow
Write-Host "  http://localhost:1985/api/v1/streams/" -ForegroundColor White
Write-Host ""
Write-Host "Logs anzeigen:" -ForegroundColor Cyan
Write-Host "  docker-compose logs -f srs" -ForegroundColor White
Write-Host "  docker exec srs-streaming-server tail -f /var/log/supervisor/stream-check.log" -ForegroundColor White
