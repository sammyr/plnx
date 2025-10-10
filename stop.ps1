Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Streaming Server - Stop" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

function Invoke-Compose {
    param([string]$ComposeArgs)

    if (Get-Command docker-compose -ErrorAction SilentlyContinue) {
        docker-compose $ComposeArgs
        return $LASTEXITCODE
    } elseif (Get-Command docker -ErrorAction SilentlyContinue) {
        docker compose $ComposeArgs
        return $LASTEXITCODE
    } else {
        Write-Host "[FEHLER] Docker / Docker Compose nicht gefunden." -ForegroundColor Red
        return 1
    }
}

Write-Host "[1/2] Container stoppen: compose stop" -ForegroundColor Yellow
$code = Invoke-Compose "stop"
if ($code -ne 0) {
    Write-Host "  [HINWEIS] compose stop meldete Code $code" -ForegroundColor DarkYellow
} else {
    Write-Host "  [OK] Container gestoppt" -ForegroundColor Green
}

Write-Host "[2/2] Container entfernen: compose down" -ForegroundColor Yellow
$code = Invoke-Compose "down"
if ($code -ne 0) {
    Write-Host "  [HINWEIS] compose down meldete Code $code" -ForegroundColor DarkYellow
} else {
    Write-Host "  [OK] Container entfernt" -ForegroundColor Green
}

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Server gestoppt!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan