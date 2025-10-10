param(
    [string]$RoomId = "Driver-Berlin-001",
    [string]$InputFile = ""
)

Write-Host "[Auto] RTMP-Preview Publisher" -ForegroundColor Yellow

if (-not (Get-Command ffmpeg -ErrorAction SilentlyContinue)) {
    Write-Host "[Auto] ffmpeg nicht gefunden. Überspringe RTMP-Preview." -ForegroundColor DarkYellow
    exit 0
}

# Standard-Input falls nicht angegeben
if ([string]::IsNullOrWhiteSpace($InputFile)) {
    $InputFile = Join-Path $PSScriptRoot "..\html\videos\ttr.m4v"
}

if (-not (Test-Path $InputFile)) {
    Write-Host "[Auto] Input-Datei nicht gefunden: $InputFile" -ForegroundColor DarkYellow
    exit 0
}

$rtmpUrl = "rtmp://localhost:1935/live/$RoomId"

Write-Host "[Auto] Publishe $InputFile -> $rtmpUrl (Hintergrund)" -ForegroundColor Yellow

# Prüfe ob bereits ein ffmpeg Publisher für diesen Raum läuft
$existing = Get-CimInstance Win32_Process | Where-Object { $_.Name -match "ffmpeg" -and $_.CommandLine -match [regex]::Escape($rtmpUrl) }
if ($existing) {
    Write-Host "[Auto] ffmpeg Publisher läuft bereits für $RoomId (PID: $($existing.ProcessId))" -ForegroundColor Green
    exit 0
}

$ffArgs = @(
    "-re",
    "-stream_loop","-1",
    "-i", $InputFile,
    "-c:v","libx264",
    "-preset","veryfast",
    "-pix_fmt","yuv420p",
    "-an",
    "-f","flv",
    $rtmpUrl
)

Start-Process ffmpeg -ArgumentList $ffArgs -WindowStyle Hidden
Write-Host "  [OK] RTMP-Preview Publisher gestartet für $RoomId" -ForegroundColor Green
