# Upload Poster-Bilder zum Server
# PowerShell Script

$SERVER = "sammyrichter.de"
$REMOTE_PATH = "/var/www/html/videos"
$LOCAL_PATH = "d:\___SYSTEM\Desktop\_PYTHON\stream\html\videos"

Write-Host "🚀 Upload Poster-Bilder zum Server..." -ForegroundColor Cyan

# Prüfe ob Dateien existieren
$posters = @(
    "ttr_poster.jpg",
    "Horrible-Boss_poster.jpg", 
    "StingrayIntro_poster.jpg"
)

foreach ($poster in $posters) {
    $localFile = Join-Path $LOCAL_PATH $poster
    
    if (Test-Path $localFile) {
        Write-Host "✅ Gefunden: $poster" -ForegroundColor Green
        
        # Upload via Docker CP (da die Dateien im Container sein müssen)
        Write-Host "   📤 Uploade $poster..." -ForegroundColor Yellow
        
        # Kopiere lokal zum Server (du musst dies manuell per SCP machen)
        # Oder verwende Docker CP wenn du SSH Zugang hast
        Write-Host "   ⚠️  Bitte manuell hochladen mit:" -ForegroundColor Yellow
        Write-Host "   scp `"$localFile`" root@${SERVER}:${REMOTE_PATH}/" -ForegroundColor White
        
    } else {
        Write-Host "❌ Nicht gefunden: $poster" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "📋 Zusammenfassung:" -ForegroundColor Cyan
Write-Host "Lokaler Pfad: $LOCAL_PATH" -ForegroundColor White
Write-Host "Server: $SERVER" -ForegroundColor White
Write-Host "Remote Pfad: $REMOTE_PATH" -ForegroundColor White
Write-Host ""
Write-Host "🔧 Nächste Schritte:" -ForegroundColor Cyan
Write-Host "1. Verbinde per SSH: ssh root@$SERVER" -ForegroundColor White
Write-Host "2. Erstelle Poster direkt auf dem Server:" -ForegroundColor White
Write-Host "   cd $REMOTE_PATH" -ForegroundColor Gray
Write-Host "   ffmpeg -ss 00:00:10 -i ttr.m4v -vframes 1 -q:v 2 -vf 'scale=1280:-1' ttr_poster.jpg" -ForegroundColor Gray
Write-Host "   ffmpeg -ss 00:00:10 -i Horrible-Boss.m4v -vframes 1 -q:v 2 -vf 'scale=1280:-1' Horrible-Boss_poster.jpg" -ForegroundColor Gray
Write-Host "   ffmpeg -ss 00:00:10 -i StingrayIntro.mp4 -vframes 1 -q:v 2 -vf 'scale=1280:-1' StingrayIntro_poster.jpg" -ForegroundColor Gray
Write-Host "3. Setze Berechtigungen:" -ForegroundColor White
Write-Host "   chmod 644 *_poster.jpg" -ForegroundColor Gray
