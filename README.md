# 🎥 SRS Streaming Server - Vollständige Anleitung

## 📋 Übersicht

Dieses Setup unterstützt:
- ⚡ **WebRTC** - Echtzeit-Streaming im Browser (~100-300ms Latenz) **NEU!**
- ✅ **RTMP** - Real-Time Messaging Protocol (Port 1935)
- ✅ **HLS** - HTTP Live Streaming (Port 8080)
- ✅ **HTTP-FLV** - Flash Video über HTTP (Port 8080)
- ✅ **HTTP API** - REST API für Server-Management (Port 1985)

---

## 🚀 Installation und Start

### Schritt 1: Docker Desktop starten

**WICHTIG:** Docker Desktop muss laufen, bevor du den Container starten kannst!

1. Öffne **Docker Desktop** über das Windows-Startmenü
2. Warte, bis Docker vollständig gestartet ist (grünes Symbol in der Taskleiste)
3. Falls Docker Desktop nicht installiert ist:
   - Download: https://www.docker.com/products/docker-desktop/
   - Installiere Docker Desktop für Windows
   - Starte den Computer neu (falls erforderlich)

### Schritt 2: Docker-Container starten

Öffne PowerShell oder CMD in diesem Verzeichnis und führe aus:

```powershell
docker-compose up -d
```

**Beim ersten Start** wird das SRS-Image heruntergeladen (~200 MB). Das dauert ein paar Minuten.

### Schritt 3: Container-Status prüfen

```powershell
docker-compose ps
```

Du solltest sehen:
```
NAME                    STATUS              PORTS
srs-streaming-server    Up X seconds        0.0.0.0:1935->1935/tcp, 0.0.0.0:1985->1985/tcp, 0.0.0.0:8080->8080/tcp
```

### Schritt 4: Logs anzeigen (optional)

```powershell
docker-compose logs -f
```

Drücke `Ctrl+C` zum Beenden der Log-Anzeige.

---

## 🎬 Stream senden mit OBS Studio

### OBS Studio konfigurieren:

1. **Einstellungen** → **Stream**
2. **Service:** Benutzerdefiniert
3. **Server:** `rtmp://localhost:1935/live`
4. **Stream-Schlüssel:** `livestream`
5. Klicke auf **OK**
6. Klicke auf **Streaming starten**

### Alternative Stream-Software:

#### FFmpeg (Teststream von Datei):
```powershell
ffmpeg -re -i video.mp4 -c copy -f flv rtmp://localhost:1935/live/livestream
```

#### FFmpeg (Webcam-Stream):
```powershell
ffmpeg -f dshow -i video="Integrierte Webcam" -f dshow -i audio="Mikrofon" -c:v libx264 -preset ultrafast -tune zerolatency -c:a aac -f flv rtmp://localhost:1935/live/livestream
```

---

## 🌐 Stream im Browser ansehen

### ⚡ Methode 1: WebRTC (Echtzeit - EMPFOHLEN!)

**Minimale Latenz (~100-300ms)!**

**Broadcaster (Stream senden):**
```
http://localhost:8080/stream.html
```
1. Kamera starten
2. Broadcast starten
3. Room-ID merken

**Viewer (Stream empfangen):**
```
http://localhost:8080/viewer.html
```
1. Room-ID eingeben ODER "Verfügbare Streams" klicken
2. "Stream verbinden" klicken

📖 **Ausführliche Anleitung:** Siehe `WEBRTC-ANLEITUNG.md`

---

### Methode 2: HTML-Player (für OBS/RTMP-Streams)

Öffne im Browser:
```
http://localhost:8080/index.html
```

Dieser Player unterstützt:
- **HLS** (HTTP Live Streaming) - beste Kompatibilität
- **HTTP-FLV** (Flash Video) - niedrige Latenz

### Methode 2: Direkte URLs

#### HLS Stream:
```
http://localhost:8080/live/livestream.m3u8
```

**Abspielen mit:**
- VLC Media Player: Datei → Netzwerkstream öffnen
- Browser mit HLS-Unterstützung (Safari, Edge)

#### HTTP-FLV Stream:
```
http://localhost:8080/live/livestream.flv
```

**Abspielen mit:**
- VLC Media Player
- Browser mit FLV.js Player

---

## 🔧 Nützliche Docker-Befehle

### Container stoppen:
```powershell
docker-compose stop
```

### Container starten (nach Stopp):
```powershell
docker-compose start
```

### Container neu starten:
```powershell
docker-compose restart
```

### Container stoppen und entfernen:
```powershell
docker-compose down
```

### Container-Logs anzeigen:
```powershell
docker-compose logs -f
```

### In den Container einsteigen (Shell):
```powershell
docker exec -it srs-streaming-server bash
```

---

## 🔍 Problembehandlung

### Problem: "Docker Desktop ist nicht gestartet"

**Lösung:**
1. Starte Docker Desktop manuell
2. Warte bis das Docker-Symbol in der Taskleiste grün ist
3. Versuche den Befehl erneut

### Problem: "Port bereits belegt"

**Lösung:**
```powershell
# Prüfe welcher Prozess den Port verwendet
netstat -ano | findstr :1935
netstat -ano | findstr :8080

# Stoppe den Prozess oder ändere die Ports in docker-compose.yml
```

### Problem: "Stream wird nicht angezeigt"

**Checkliste:**
1. ✅ Ist der Docker-Container gestartet? → `docker-compose ps`
2. ✅ Sendet OBS einen Stream? → Prüfe OBS-Status
3. ✅ Ist die URL korrekt? → `http://localhost:8080/live/livestream.m3u8`
4. ✅ Firewall-Blockierung? → Erlaube Port 1935, 8080, 1985

### Problem: "Hohe Latenz"

**Lösung:**
- Verwende HTTP-FLV statt HLS (niedrigere Latenz)
- In OBS: Reduziere Keyframe-Intervall auf 1 Sekunde
- In OBS: Aktiviere "Niedrige Latenz" in den erweiterten Einstellungen

---

## 📊 HTTP API Endpunkte

### Server-Informationen:
```
http://localhost:1985/api/v1/versions
```

### Aktive Streams:
```
http://localhost:1985/api/v1/streams/
```

### Clients anzeigen:
```
http://localhost:1985/api/v1/clients/
```

### Vollständige API-Dokumentation:
```
http://localhost:1985/api/v1/
```

---

## 📁 Dateistruktur

```
stream/
├── docker-compose.yml      # Docker-Konfiguration
├── srs.conf               # SRS-Server-Konfiguration
├── html/
│   └── index.html         # Web-Player für Browser
└── README.md              # Diese Anleitung
```

---

## 🎯 Stream-URLs Übersicht

| Protokoll | URL | Port | Verwendung |
|-----------|-----|------|------------|
| **RTMP Publish** | `rtmp://localhost:1935/live/livestream` | 1935 | Stream senden (OBS) |
| **HLS Play** | `http://localhost:8080/live/livestream.m3u8` | 8080 | Browser-Wiedergabe |
| **HTTP-FLV Play** | `http://localhost:8080/live/livestream.flv` | 8080 | Browser-Wiedergabe |
| **HTTP API** | `http://localhost:1985/api/v1/` | 1985 | Server-Management |
| **Web Player** | `http://localhost:8080/index.html` | 8080 | Test-Player |

---

## ⚙️ Erweiterte Konfiguration

### Mehrere Streams gleichzeitig

Du kannst mehrere Streams verwenden, indem du den Stream-Namen änderst:

**Stream 1:**
- Publish: `rtmp://localhost:1935/live/stream1`
- Play HLS: `http://localhost:8080/live/stream1.m3u8`

**Stream 2:**
- Publish: `rtmp://localhost:1935/live/stream2`
- Play HLS: `http://localhost:8080/live/stream2.m3u8`

### Aufnahme aktivieren (DVR)

Bearbeite `srs.conf` und ändere:
```conf
dvr {
    enabled         on;
    dvr_path        ./objs/nginx/html/[app]/[stream].[timestamp].flv;
    dvr_plan        session;
}
```

Dann Container neu starten:
```powershell
docker-compose restart
```

### Transcoding aktivieren

Für verschiedene Qualitätsstufen (z.B. 720p, 480p) kann Transcoding aktiviert werden.
Siehe `srs.conf` → Abschnitt `transcode`.

---

## 🌍 Zugriff aus dem Netzwerk

Um von anderen Geräten im Netzwerk zuzugreifen:

1. Finde deine lokale IP-Adresse:
```powershell
ipconfig
```

2. Ersetze `localhost` mit deiner IP (z.B. `192.168.1.100`):
```
rtmp://192.168.1.100:1935/live/livestream
http://192.168.1.100:8080/live/livestream.m3u8
```

3. **Firewall-Regel erstellen:**
```powershell
# Als Administrator ausführen
netsh advfirewall firewall add rule name="SRS RTMP" dir=in action=allow protocol=TCP localport=1935
netsh advfirewall firewall add rule name="SRS HTTP" dir=in action=allow protocol=TCP localport=8080
netsh advfirewall firewall add rule name="SRS API" dir=in action=allow protocol=TCP localport=1985
```

---

## 📚 Weitere Ressourcen

- **SRS Offizielle Dokumentation:** https://ossrs.io/
- **SRS GitHub:** https://github.com/ossrs/srs
- **OBS Studio:** https://obsproject.com/
- **HLS.js:** https://github.com/video-dev/hls.js/
- **FLV.js:** https://github.com/bilibili/flv.js/

---

## ✅ Schnellstart-Checkliste

- [ ] Docker Desktop installiert und gestartet
- [ ] `docker-compose up -d` ausgeführt
- [ ] Container läuft: `docker-compose ps`
- [ ] OBS konfiguriert: `rtmp://localhost:1935/live` + Key: `livestream`
- [ ] Stream gestartet in OBS
- [ ] Browser geöffnet: `http://localhost:8080/index.html`
- [ ] Stream wird angezeigt ✅

---

## 💡 Tipps

1. **Niedrige Latenz:** Verwende HTTP-FLV statt HLS
2. **Beste Kompatibilität:** Verwende HLS für mobile Geräte
3. **Monitoring:** Nutze die HTTP API für Statistiken
4. **Backup:** Die Konfiguration ist in `srs.conf` gespeichert
5. **Performance:** Der Server kann hunderte gleichzeitige Zuschauer handhaben

---

**Viel Erfolg mit deinem Streaming-Server! 🚀**
