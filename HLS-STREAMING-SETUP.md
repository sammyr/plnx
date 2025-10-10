# HLS-Streaming Setup (Docker)

## Übersicht

Die Demo-Videos werden automatisch als HLS-Streams bereitgestellt über:
- **FFmpeg**: Konvertiert MP4 → RTMP
- **SRS**: Konvertiert RTMP → HLS
- **Docker**: Alles läuft in Containern

## Architektur

```
Video-Dateien (html/videos/*.mp4)
    ↓ FFmpeg (demo-streams Container)
RTMP-Stream (rtmp://srs:1935/live/*)
    ↓ SRS (srs Container)
HLS-Stream (http://localhost:8081/live/*.m3u8)
    ↓ HLS.js
Browser
```

## Starten

### Alle Services starten (inkl. Demo-Streams):
```bash
docker-compose up -d
```

Das startet automatisch:
- ✅ SRS Streaming-Server (Port 8081)
- ✅ PHP Web-Server (Port 8080)
- ✅ WebRTC Signaling-Server (Port 3000)
- ✅ Demo-Video-Streams (4 Videos)

### Nur Demo-Streams neu starten:
```bash
docker-compose restart demo-streams
```

### Logs anzeigen:
```bash
# Alle Logs
docker-compose logs -f

# Nur Demo-Streams
docker-compose logs -f demo-streams

# Nur SRS
docker-compose logs -f srs
```

## Verfügbare Streams

Nach dem Start sind folgende HLS-Streams verfügbar:

| Video | Stream-Key | HLS-URL |
|-------|-----------|---------|
| Die Totale Erinnerung | demo_video_stream | http://localhost:8081/live/demo_video_stream.m3u8 |
| Stingray - Intro | demo_stingray_stream | http://localhost:8081/live/demo_stingray_stream.m3u8 |
| Subway | demo_subway_stream | http://localhost:8081/live/demo_subway_stream.m3u8 |
| Chuck und Larry | demo_chuck_stream | http://localhost:8081/live/demo_chuck_stream.m3u8 |

## Testen

### Im Browser:
```
http://localhost:8080/watch-movie.php?room=demo_video_stream
```

### Console sollte zeigen:
```
Versuche HLS-Stream: http://localhost:8081/live/ttr.m3u8
Verwende HLS.js für Streaming
HLS-Manifest geladen, Stream bereit ✅
```

### Direkt HLS-Stream testen:
```
http://localhost:8081/live/demo_video_stream.m3u8
```

## Fallback-Strategie

Die `watch-movie.php` implementiert automatisch:
1. **Versuch HLS-Stream** (Port 8081)
2. **Bei Fehler**: Fallback zu direkter MP4
3. **Console-Log**: Zeigt welche Methode verwendet wird

## Stoppen

### Alle Services stoppen:
```bash
docker-compose down
```

### Nur Demo-Streams stoppen:
```bash
docker-compose stop demo-streams
```

## Troubleshooting

### Streams starten nicht:
```bash
# Prüfe Container-Status
docker-compose ps

# Prüfe Logs
docker-compose logs demo-streams

# Prüfe ob Videos existieren
ls -lh html/videos/
```

### HLS nicht verfügbar:
```bash
# Prüfe SRS-Status
docker-compose logs srs

# Teste HLS-URL direkt
curl -I http://localhost:8081/live/demo_video_stream.m3u8
```

### CORS-Fehler:
- SRS hat CORS bereits aktiviert (`crossdomain on` in srs.conf)
- Prüfe Browser-Console für Details

## Für Ubuntu Server

Auf dem Server:
```bash
# Docker installieren
sudo apt-get update
sudo apt-get install docker.io docker-compose

# Repository klonen
git clone <repo-url>
cd stream

# Starten
docker-compose up -d

# Status prüfen
docker-compose ps
docker-compose logs -f
```

## Ressourcen

Pro Stream:
- **CPU**: ~5-10% (mit `-c copy`, kein Re-Encoding)
- **RAM**: ~50-100 MB
- **Bandbreite**: Adaptive (HLS)

Alle 4 Demo-Streams:
- **CPU**: ~20-40%
- **RAM**: ~200-400 MB
- **Disk**: Minimal (HLS-Segmente werden automatisch gelöscht)

## Vorteile

✅ **Keine PowerShell nötig** - Läuft auf Linux/Windows/Mac
✅ **Automatischer Start** - Docker startet Streams automatisch
✅ **Niedrige Latenz** - 2-6 Sekunden (konfigurierbar)
✅ **Adaptive Qualität** - Browser wählt beste Qualität
✅ **Segmentierte Übertragung** - Nur kleine .ts-Dateien
✅ **Fallback** - MP4 wenn HLS nicht verfügbar
