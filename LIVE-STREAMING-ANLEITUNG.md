# 🔴 Live-Streaming wie Stripchat - Anleitung

## 🎯 Übersicht

Du hast jetzt ein **professionelles Live-Streaming-System** wie Stripchat:
- ✅ RTMP-Streaming (wie Twitch/YouTube)
- ✅ Ultra-niedrige Latenz (1-2 Sekunden)
- ✅ HTTP-FLV Player (professionell)
- ✅ Mehrere Zuschauer gleichzeitig
- ✅ HD-Qualität

---

## 🚀 Schnellstart

### 1. Server starten (falls nicht läuft)

```powershell
docker-compose up -d
```

### 2. Mit OBS streamen

**OBS Studio öffnen:**
1. Einstellungen → Stream
2. **Server:** `rtmp://localhost:1935/live`
3. **Stream-Key:** `livestream`
4. Klicke "Streaming starten"

### 3. Stream ansehen

**Öffne im Browser:**
```
http://localhost:8080/live.html
```

**Oder mit eigenem Stream-Namen:**
```
http://localhost:8080/live.html?stream=DEIN_NAME
```

---

## 📺 Wie es funktioniert

### Workflow:

```
OBS/Webcam  →  RTMP  →  SRS Server  →  HTTP-FLV  →  Browser
   (Du)         :1935      (Docker)       :8080      (Zuschauer)
```

### Technologie:

1. **RTMP (Real-Time Messaging Protocol)**
   - Standard für Live-Streaming
   - Verwendet von Twitch, YouTube, etc.
   - Port 1935

2. **SRS (Simple Realtime Server)**
   - Konvertiert RTMP zu HTTP-FLV/HLS
   - Ultra-niedrige Latenz
   - Professioneller Streaming-Server

3. **HTTP-FLV**
   - Streaming über HTTP
   - Latenz: 1-2 Sekunden
   - Besser als HLS (5-10 Sekunden)

---

## 🎥 OBS Studio Setup

### Installation:

1. Download: https://obsproject.com/
2. Installieren
3. OBS öffnen

### Konfiguration:

**Einstellungen → Stream:**
```
Service:         Benutzerdefiniert
Server:          rtmp://localhost:1935/live
Stream-Schlüssel: livestream
```

**Einstellungen → Ausgabe:**
```
Ausgabemodus:    Einfach
Video-Bitrate:   2500 Kbps (für HD)
Encoder:         x264
Audio-Bitrate:   160 Kbps
```

**Einstellungen → Video:**
```
Basis-Auflösung:     1920x1080
Ausgabe-Auflösung:   1920x1080
FPS:                 30
```

### Szene einrichten:

1. **Quellen hinzufügen:**
   - Video-Aufnahmegerät (Webcam)
   - Audio-Eingang (Mikrofon)
   - Optional: Bilder, Text, etc.

2. **Streaming starten:**
   - Klicke "Streaming starten"
   - Warte 2-3 Sekunden
   - Öffne `http://localhost:8080/live.html`

---

## 🌐 URLs

### Für Broadcaster (OBS):
```
RTMP URL:    rtmp://localhost:1935/live
Stream Key:  livestream
```

### Für Zuschauer (Browser):
```
Live-Seite:  http://localhost:8080/live.html
FLV-Stream:  http://localhost:8080/live/livestream.flv
HLS-Stream:  http://localhost:8080/live/livestream.m3u8
```

### Mit eigenem Stream-Namen:
```
RTMP:        rtmp://localhost:1935/live/MEIN_NAME
Browser:     http://localhost:8080/live.html?stream=MEIN_NAME
```

---

## 💎 Features der Live-Seite

### Design wie Stripchat:
- ✅ Professioneller Video-Player
- ✅ LIVE-Badge mit Animation
- ✅ Zuschauer-Anzahl (live)
- ✅ Stream-Dauer
- ✅ Qualitäts-Auswahl
- ✅ Technische Infos
- ✅ Dunkles, edles Design

### Technische Features:
- ✅ HTTP-FLV (niedrige Latenz)
- ✅ Automatisches Abspielen
- ✅ Fehler-Behandlung
- ✅ Responsive Design
- ✅ Mehrere Zuschauer

---

## 🔧 Erweiterte Konfiguration

### Mehrere Streams gleichzeitig:

**Broadcaster 1:**
```
RTMP: rtmp://localhost:1935/live/stream1
URL:  http://localhost:8080/live.html?stream=stream1
```

**Broadcaster 2:**
```
RTMP: rtmp://localhost:1935/live/stream2
URL:  http://localhost:8080/live.html?stream=stream2
```

### Verschiedene Qualitäten:

In `srs.conf` Transcode aktivieren für Auto-Qualität:
```
transcode {
    enabled     on;
    # HD, SD, Low automatisch
}
```

---

## 📊 Monitoring

### Stream-Status prüfen:

**HTTP API:**
```
http://localhost:1985/api/v1/streams/
```

**Response zeigt:**
- Aktive Streams
- Zuschauer-Anzahl
- Bitrate
- Dauer

### Logs anzeigen:

```powershell
docker-compose logs -f srs
```

---

## 🌍 Für Hetzner Server

### Nach Deployment:

**Broadcaster (OBS):**
```
Server:      rtmp://DEINE_SERVER_IP:1935/live
Stream Key:  livestream
```

**Zuschauer:**
```
http://DEINE_SERVER_IP/live.html
```

**Mit Domain:**
```
rtmp://stream.deine-domain.de:1935/live
https://stream.deine-domain.de/live.html
```

---

## 🎨 Anpassungen

### Stream-Titel ändern:

In `live.html` Zeile 310:
```javascript
const streamName = urlParams.get('stream') || 'livestream';
```

### Design anpassen:

CSS-Variablen in `live.html`:
```css
--bg-primary: #0a0a0f;
--accent: #6366f1;
--accent-gold: #d4af37;
```

### Zuschauer-Anzahl (echt):

Ersetze Simulation durch API-Call:
```javascript
fetch(`http://localhost:1985/api/v1/streams/`)
    .then(r => r.json())
    .then(data => {
        // Echte Zuschauer-Anzahl
    });
```

---

## 🐛 Problembehandlung

### Stream wird nicht angezeigt:

**1. Prüfe ob OBS streamt:**
- OBS zeigt "LIVE" in grün?
- Bitrate > 0?

**2. Prüfe SRS-Logs:**
```powershell
docker-compose logs srs
```

Du solltest sehen:
```
RTMP publish stream=livestream
```

**3. Teste FLV-URL direkt:**
```
http://localhost:8080/live/livestream.flv
```

### Hohe Latenz:

**In OBS:**
- Einstellungen → Erweitert
- Stream-Verzögerung: 0 Sekunden
- Keyframe-Intervall: 2 Sekunden

**In srs.conf:**
```
min_latency     on;
```

### Ruckeln/Buffering:

**OBS Bitrate reduzieren:**
- Einstellungen → Ausgabe
- Video-Bitrate: 1500 Kbps (statt 2500)

---

## ✅ Checkliste

- [ ] Docker-Container laufen
- [ ] OBS installiert und konfiguriert
- [ ] RTMP-URL korrekt: `rtmp://localhost:1935/live`
- [ ] Stream-Key: `livestream`
- [ ] OBS zeigt "LIVE"
- [ ] Browser öffnet: `http://localhost:8080/live.html`
- [ ] Video wird abgespielt
- [ ] Latenz < 3 Sekunden

---

## 🎉 Fertig!

Du hast jetzt ein **professionelles Live-Streaming-System** wie Stripchat!

**Starte OBS → Klicke "Streaming starten" → Öffne `http://localhost:8080/live.html`**

Bei Fragen: Prüfe die Logs mit `docker-compose logs -f`
