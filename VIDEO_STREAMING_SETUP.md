# Video-Streaming über HLS Setup

## Problem
Videos werden direkt als MP4-Datei geladen (20+ MB), was zu:
- Hoher Bandbreite führt
- Langsamem Start
- Keiner adaptiven Qualität

## Lösung: HLS-Streaming über SRS

### Architektur
```
Video-Datei (MP4)
    ↓ FFmpeg
RTMP-Stream
    ↓ SRS
HLS-Stream (.m3u8 + .ts Segmente)
    ↓ HLS.js / Native
Browser
```

## Installation FFmpeg

### Windows:
```powershell
# Mit Chocolatey
choco install ffmpeg

# Oder manuell von https://ffmpeg.org/download.html
```

### Prüfen:
```powershell
ffmpeg -version
```

## Video als Stream bereitstellen

### 1. Video-Datei streamen:
```powershell
# Beispiel: Stingray-Video
.\stream-video-file.ps1 -VideoFile "html/videos/StingrayIntro.mp4" -StreamKey "demo_stingray_stream"

# Beispiel: Subway-Video
.\stream-video-file.ps1 -VideoFile "html/videos/Subway.m4v" -StreamKey "demo_subway_stream"
```

### 2. Stream ist verfügbar unter:
```
HLS: http://localhost:8080/live/demo_stingray_stream.m3u8
RTMP: rtmp://localhost/live/demo_stingray_stream
```

### 3. Browser öffnet automatisch HLS:
```
http://localhost:8080/watch.php?room=demo_stingray_stream
```

## Automatisches Streaming aller Demo-Videos

Erstelle `stream-all-demos.ps1`:
```powershell
# Starte alle Demo-Videos als Streams
Start-Process powershell -ArgumentList "-NoExit", "-Command", ".\stream-video-file.ps1 -VideoFile 'html/videos/ttr.m4v' -StreamKey 'demo_video_stream'"
Start-Sleep -Seconds 2

Start-Process powershell -ArgumentList "-NoExit", "-Command", ".\stream-video-file.ps1 -VideoFile 'html/videos/StingrayIntro.mp4' -StreamKey 'demo_stingray_stream'"
Start-Sleep -Seconds 2

Start-Process powershell -ArgumentList "-NoExit", "-Command", ".\stream-video-file.ps1 -VideoFile 'html/videos/Subway.m4v' -StreamKey 'demo_subway_stream'"
Start-Sleep -Seconds 2

Start-Process powershell -ArgumentList "-NoExit", "-Command", ".\stream-video-file.ps1 -VideoFile 'html/videos/Chuck.und.Larry.m4v' -StreamKey 'demo_chuck_stream'"

Write-Host "Alle Demo-Streams gestartet!" -ForegroundColor Green
```

## Vorteile

### Ressourcen:
- **CPU**: Niedrig (kein Re-Encoding mit `-c copy`)
- **RAM**: ~50-100 MB pro Stream
- **Bandbreite**: Adaptive Qualität (HLS)

### Performance:
- **Segmentierte Übertragung**: Nur kleine .ts-Dateien (2-10 Sekunden)
- **Adaptive Bitrate**: Browser wählt beste Qualität
- **Caching**: Segmente können gecacht werden
- **Niedrige Latenz**: 2-6 Sekunden (konfigurierbar)

### Vergleich:

| Methode | Initiale Last | Bandbreite | Latenz |
|---------|---------------|------------|--------|
| Direkte MP4 | 20+ MB | Hoch | Sofort |
| HLS-Stream | ~500 KB | Niedrig | 2-6s |

## Fallback-Strategie

watch.php implementiert automatisch:
1. **Versuch HLS-Stream** (`/live/demo_xxx.m3u8`)
2. **Bei Fehler**: Fallback zu direkter MP4
3. **Konsolen-Log**: Zeigt welche Methode verwendet wird

## Für Hetzner Server

Auf dem Server:
```bash
# FFmpeg installieren
apt-get install ffmpeg

# Video streamen
ffmpeg -re -stream_loop -1 -i /path/to/video.mp4 \
    -c:v copy \
    -c:a aac -b:a 128k \
    -f flv rtmp://localhost/live/stream_key
```

## Monitoring

Prüfe aktive Streams:
```
http://localhost:1985/api/v1/streams/
```

## Troubleshooting

### Stream startet nicht:
```powershell
# Prüfe ob SRS läuft
docker ps | Select-String srs

# Prüfe FFmpeg
ffmpeg -version
```

### HLS nicht verfügbar:
- Browser-Konsole öffnen (F12)
- Sollte zeigen: "Verwende HLS.js für Streaming" oder "HLS-Stream nicht verfügbar, verwende MP4-Fallback"

### Hohe CPU-Last:
- Verwende `-c copy` (kein Re-Encoding)
- Reduziere Anzahl paralleler Streams
