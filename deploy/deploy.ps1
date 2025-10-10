# PowerShell Deployment Script für Windows
# Verwendung: .\deploy.ps1 -ServerIP "123.45.67.89"

param(
    [Parameter(Mandatory=$true)]
    [string]$ServerIP,
    
    [Parameter(Mandatory=$false)]
    [string]$ServerUser = "root"
)

$RemoteDir = "/opt/stream"

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "  Deployment zu Hetzner Server" -ForegroundColor Cyan
Write-Host "  Server: $ServerIP" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

# Prüfe ob SSH/SCP verfügbar ist
if (-not (Get-Command ssh -ErrorAction SilentlyContinue)) {
    Write-Host "[FEHLER] SSH nicht gefunden!" -ForegroundColor Red
    Write-Host "Installiere OpenSSH oder verwende WSL" -ForegroundColor Yellow
    exit 1
}

# 1. Erstelle Verzeichnisse auf Server
Write-Host "[1/5] Erstelle Verzeichnisse auf Server..." -ForegroundColor Yellow
ssh ${ServerUser}@${ServerIP} "mkdir -p $RemoteDir/{html,deploy,signaling-server,logs}"
Write-Host "  [OK] Verzeichnisse erstellt" -ForegroundColor Green

# 2. Lade HTML-Dateien hoch
Write-Host ""
Write-Host "[2/5] Lade HTML-Dateien hoch..." -ForegroundColor Yellow
scp -r ..\html\* ${ServerUser}@${ServerIP}:${RemoteDir}/html/
Write-Host "  [OK] HTML-Dateien hochgeladen" -ForegroundColor Green

# 3. Lade Docker-Konfiguration hoch
Write-Host ""
Write-Host "[3/5] Lade Docker-Konfiguration hoch..." -ForegroundColor Yellow
scp docker-compose.production.yml ${ServerUser}@${ServerIP}:${RemoteDir}/deploy/
scp nginx.conf ${ServerUser}@${ServerIP}:${RemoteDir}/deploy/
scp ..\srs.conf ${ServerUser}@${ServerIP}:${RemoteDir}/
Write-Host "  [OK] Docker-Konfiguration hochgeladen" -ForegroundColor Green

# 4. Lade Signaling-Server hoch
Write-Host ""
Write-Host "[4/5] Lade Signaling-Server hoch..." -ForegroundColor Yellow
scp -r ..\signaling-server\* ${ServerUser}@${ServerIP}:${RemoteDir}/signaling-server/
Write-Host "  [OK] Signaling-Server hochgeladen" -ForegroundColor Green

# 5. Führe Setup-Script aus
Write-Host ""
Write-Host "[5/5] Fuehre Server-Setup aus..." -ForegroundColor Yellow
scp setup-server.sh ${ServerUser}@${ServerIP}:/tmp/
ssh ${ServerUser}@${ServerIP} "chmod +x /tmp/setup-server.sh && /tmp/setup-server.sh"
Write-Host "  [OK] Setup abgeschlossen" -ForegroundColor Green

Write-Host ""
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "  Deployment abgeschlossen!" -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Server-URL: http://$ServerIP" -ForegroundColor White
Write-Host ""
Write-Host "Naechste Schritte:" -ForegroundColor Yellow
Write-Host "  1. Teste die Seite: http://$ServerIP" -ForegroundColor White
Write-Host "  2. SSL einrichten:  ssh root@$ServerIP" -ForegroundColor White
Write-Host "  3. Logs anzeigen:   ssh root@$ServerIP 'cd /opt/stream/deploy && docker-compose logs -f'" -ForegroundColor White
Write-Host ""
