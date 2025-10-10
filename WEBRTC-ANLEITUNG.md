# ⚡ WebRTC Echtzeit-Streaming - Anleitung

## 🎯 Übersicht

**WebRTC (Web Real-Time Communication)** ist die beste Technologie für Echtzeit-Streaming im Browser!

### ✅ Vorteile:
- **Minimale Latenz:** ~100-300ms (fast Echtzeit!)
- **Peer-to-Peer:** Direkte Verbindung zwischen Browsern
- **Hohe Qualität:** Adaptive Bitrate
- **Keine Plugins:** Funktioniert nativ im Browser
- **Mehrere Zuschauer:** Unbegrenzt möglich

### 📊 Latenz-Vergleich:

| Technologie | Latenz | Verwendung |
|-------------|--------|------------|
| **WebRTC** | ~100-300ms | ⭐ **Echtzeit-Streaming** |
| HTTP-FLV | ~1-3 Sekunden | Live-Streaming |
| HLS | ~5-10 Sekunden | VOD/Live |
| RTMP | ~2-5 Sekunden | Broadcasting |

---

## 🚀 Installation & Start

### Schritt 1: Docker-Container starten

```powershell
docker-compose up -d
```

Dies startet:
- **SRS Server** (Port 8080) - für HTML-Dateien
- **WebRTC Signaling Server** (Port 3000) - für WebRTC-Verbindungen

### Schritt 2: Prüfe ob alles läuft

```powershell
docker-compose ps
```

Du solltest sehen:
```
NAME                        STATUS
srs-streaming-server        Up
webrtc-signaling-server     Up
```

### Schritt 3: Logs anzeigen (optional)

```powershell
# Alle Logs
docker-compose logs -f

# Nur Signaling-Server
docker-compose logs -f webrtc-signaling
```

---

## 📹 Streaming starten

### Als Broadcaster (Stream senden):

1. **Öffne im Browser:**
   ```
   http://localhost:8080/stream.html
   ```

2. **Kamera starten:**
   - Klicke "📷 Kamera starten"
   - Erlaube Kamera- und Mikrofon-Zugriff
   - Wähle deine Kamera und Qualität

3. **Broadcast starten:**
   - Klicke "🔴 Broadcast starten"
   - Du erhältst eine **Room-ID** (z.B. `room_abc123`)
   - Diese ID teilst du mit Zuschauern

4. **Stream läuft!**
   - Status zeigt "🔴 LIVE"
   - Zuschauer-Anzahl wird angezeigt
   - Statistiken werden aktualisiert

### Als Viewer (Stream empfangen):

1. **Öffne im Browser:**
   ```
   http://localhost:8080/viewer.html
   ```

2. **Verbindung herstellen:**
   
   **Option A - Manuelle Eingabe:**
   - Gib die Room-ID vom Broadcaster ein
   - Klicke "▶️ Stream verbinden"

   **Option B - Automatisch:**
   - Klicke "🔄 Verfügbare Streams"
   - Wähle einen Stream aus der Liste

3. **Stream genießen!**
   - Minimale Latenz (~100-300ms)
   - Hohe Qualität
   - Echtzeit-Erlebnis

---

## 🏗️ Architektur

### Wie funktioniert WebRTC?

```
┌─────────────┐                  ┌──────────────────┐                  ┌─────────────┐
│             │   WebSocket      │                  │   WebSocket      │             │
│ Broadcaster │◄────────────────►│ Signaling Server │◄────────────────►│   Viewer    │
│             │  (Signaling)     │   (Port 3000)    │  (Signaling)     │             │
└─────────────┘                  └──────────────────┘                  └─────────────┘
       │                                                                        │
       │                                                                        │
       │                        WebRTC P2P Connection                           │
       │                        (Audio/Video Stream)                            │
       └────────────────────────────────────────────────────────────────────────┘
                                    Direct Connection
```

### Komponenten:

1. **Signaling Server (Node.js + WebSocket)**
   - Vermittelt Verbindungen
   - Tauscht SDP-Offers/Answers aus
   - Verwaltet ICE-Candidates
   - Tracked aktive Räume

2. **Broadcaster (stream.html)**
   - Erfasst Webcam/Mikrofon
   - Erstellt WebRTC PeerConnections
   - Sendet Stream an Viewer

3. **Viewer (viewer.html)**
   - Empfängt WebRTC-Stream
   - Zeigt Video/Audio an
   - Minimale Latenz

4. **STUN Server (Google)**
   - Hilft bei NAT-Traversal
   - Ermöglicht P2P-Verbindungen

---

## 🔧 Konfiguration

### Qualitätseinstellungen (stream.html):

| Qualität | Auflösung | Empfohlene Bitrate | Verwendung |
|----------|-----------|-------------------|------------|
| **Low** | 640x360 | ~500 Kbps | Langsame Verbindung |
| **SD** | 854x480 | ~1000 Kbps | Standard |
| **HD** | 1280x720 | ~2500 Kbps | ⭐ Empfohlen |
| **Full HD** | 1920x1080 | ~4000 Kbps | Schnelle Verbindung |

### Signaling-Server anpassen:

Bearbeite `signaling-server/server.js`:

```javascript
// Port ändern
const PORT = process.env.PORT || 3000;

// Cleanup-Intervall anpassen (Standard: 1 Stunde)
const maxAge = 60 * 60 * 1000;
```

### STUN/TURN Server ändern:

In `stream.html` und `viewer.html`:

```javascript
const ICE_SERVERS = [
    { urls: 'stun:stun.l.google.com:19302' },
    // Eigener TURN-Server (für schwierige Netzwerke)
    {
        urls: 'turn:your-turn-server.com:3478',
        username: 'user',
        credential: 'pass'
    }
];
```

---

## 🌐 Zugriff aus dem Netzwerk

### Lokales Netzwerk (LAN):

1. **Finde deine IP-Adresse:**
   ```powershell
   ipconfig
   ```
   Suche nach "IPv4-Adresse" (z.B. `192.168.1.100`)

2. **Firewall-Regeln erstellen:**
   ```powershell
   # Als Administrator ausführen
   netsh advfirewall firewall add rule name="WebRTC Signaling" dir=in action=allow protocol=TCP localport=3000
   netsh advfirewall firewall add rule name="SRS HTTP" dir=in action=allow protocol=TCP localport=8080
   ```

3. **URLs anpassen:**
   
   In `stream.html` und `viewer.html` ändere:
   ```javascript
   const SIGNALING_SERVER = 'ws://192.168.1.100:3000';
   ```

4. **Zugriff von anderen Geräten:**
   ```
   Broadcaster: http://192.168.1.100:8080/stream.html
   Viewer:      http://192.168.1.100:8080/viewer.html
   ```

### Internet (Öffentlich):

⚠️ **Für öffentlichen Zugriff benötigst du:**

1. **HTTPS (SSL-Zertifikat)**
   - WebRTC erfordert HTTPS für Kamera-Zugriff
   - Verwende Let's Encrypt oder Cloudflare

2. **TURN-Server**
   - Für Verbindungen hinter NAT/Firewall
   - Empfehlung: coturn, Twilio TURN

3. **Reverse Proxy**
   - Nginx oder Caddy
   - SSL-Terminierung

---

## 📊 Monitoring & Statistiken

### HTTP API Endpunkte:

```bash
# Server-Status
curl http://localhost:3000/health

# Aktive Räume
curl http://localhost:3000/rooms
```

### Response-Beispiel:

```json
{
  "status": "ok",
  "rooms": 2,
  "broadcasters": 2,
  "viewers": 5,
  "connections": 7
}
```

### Browser DevTools:

1. **Öffne Browser-Konsole** (F12)
2. **WebRTC Internals:**
   - Chrome: `chrome://webrtc-internals`
   - Firefox: `about:webrtc`
3. **Zeigt:**
   - Verbindungsstatus
   - Bitrate
   - Packet Loss
   - ICE-Candidates

---

## 🐛 Problembehandlung

### Problem: "Verbindung zum Server fehlgeschlagen"

**Lösung:**
```powershell
# Prüfe ob Signaling-Server läuft
docker-compose logs webrtc-signaling

# Neu starten
docker-compose restart webrtc-signaling
```

### Problem: "Kamera-Zugriff verweigert"

**Lösung:**
- Browser-Berechtigungen prüfen
- HTTPS verwenden (für öffentlichen Zugriff)
- Andere Anwendungen schließen (die Kamera verwenden)

### Problem: "Stream verbindet nicht"

**Checkliste:**
- [ ] Signaling-Server läuft? → `docker-compose ps`
- [ ] Room-ID korrekt?
- [ ] Broadcaster ist LIVE?
- [ ] Firewall blockiert Port 3000?
- [ ] Browser-Konsole auf Fehler prüfen

### Problem: "Hohe Latenz / Ruckeln"

**Lösungen:**
1. **Qualität reduzieren** (stream.html)
2. **TURN-Server verwenden** (bei NAT-Problemen)
3. **Netzwerk prüfen:**
   ```powershell
   ping 192.168.1.100
   ```
4. **Browser-Hardware-Beschleunigung aktivieren**

### Problem: "Verbindung bricht ab"

**Lösungen:**
- TURN-Server konfigurieren
- Firewall-Regeln prüfen
- NAT-Typ prüfen (Symmetric NAT = problematisch)

---

## 🔒 Sicherheit

### Produktions-Empfehlungen:

1. **HTTPS verwenden**
   ```nginx
   server {
       listen 443 ssl;
       ssl_certificate /path/to/cert.pem;
       ssl_certificate_key /path/to/key.pem;
   }
   ```

2. **Authentifizierung hinzufügen**
   - JWT-Tokens
   - OAuth2
   - Passwort-geschützte Räume

3. **Rate Limiting**
   ```javascript
   // In server.js
   const rateLimit = require('express-rate-limit');
   ```

4. **CORS richtig konfigurieren**
   ```javascript
   app.use(cors({
       origin: 'https://your-domain.com'
   }));
   ```

---

## 🚀 Performance-Optimierung

### Broadcaster:

1. **Hardware-Beschleunigung:**
   - Chrome: `chrome://settings/system`
   - Aktiviere "Hardwarebeschleunigung verwenden"

2. **Optimale Einstellungen:**
   ```javascript
   const constraints = {
       video: {
           width: { ideal: 1280 },
           height: { ideal: 720 },
           frameRate: { ideal: 30, max: 30 } // Nicht höher!
       }
   };
   ```

3. **Bitrate-Kontrolle:**
   ```javascript
   const sender = peerConnection.getSenders()[0];
   const parameters = sender.getParameters();
   parameters.encodings[0].maxBitrate = 2500000; // 2.5 Mbps
   await sender.setParameters(parameters);
   ```

### Signaling-Server:

1. **Clustering (mehrere Instanzen):**
   ```yaml
   # docker-compose.yml
   webrtc-signaling:
       deploy:
           replicas: 3
   ```

2. **Redis für Shared State:**
   ```javascript
   const redis = require('redis');
   const client = redis.createClient();
   ```

---

## 📚 Erweiterte Features

### Mehrere Kameras gleichzeitig:

```javascript
// In stream.html
const stream1 = await navigator.mediaDevices.getUserMedia({
    video: { deviceId: camera1Id }
});
const stream2 = await navigator.mediaDevices.getUserMedia({
    video: { deviceId: camera2Id }
});
```

### Screen-Sharing hinzufügen:

```javascript
const screenStream = await navigator.mediaDevices.getDisplayMedia({
    video: { cursor: "always" },
    audio: true
});
```

### Aufnahme (Recording):

```javascript
const mediaRecorder = new MediaRecorder(localStream);
mediaRecorder.ondataavailable = (event) => {
    // Speichere Chunks
};
mediaRecorder.start();
```

### Chat-Funktion:

```javascript
// Data Channel für Text-Chat
const dataChannel = peerConnection.createDataChannel('chat');
dataChannel.onmessage = (event) => {
    console.log('Nachricht:', event.data);
};
```

---

## 🎯 Vergleich: WebRTC vs. OBS+SRS

| Feature | WebRTC | OBS + SRS |
|---------|--------|-----------|
| **Latenz** | ~100-300ms ⭐ | ~2-5s |
| **Setup** | Einfach | Mittel |
| **Qualität** | Hoch | Sehr hoch |
| **Zuschauer** | Unbegrenzt | Unbegrenzt |
| **Browser-only** | ✅ Ja | ❌ Nein (OBS nötig) |
| **Aufnahme** | Möglich | ✅ Einfach |
| **Overlays** | Schwierig | ✅ Einfach |
| **Empfehlung** | Echtzeit-Interaktion | Professionelles Broadcasting |

---

## 💡 Use Cases

### Perfekt für:
- 🎮 **Gaming-Streams** (niedrige Latenz wichtig)
- 💬 **Video-Calls** (Echtzeit-Kommunikation)
- 🎓 **Online-Unterricht** (Interaktiv)
- 🎥 **Live-Events** (Zuschauer-Interaktion)
- 🤝 **Webinare** (Q&A in Echtzeit)

### Weniger geeignet für:
- 📺 **24/7 Streams** (besser: OBS + SRS)
- 🎬 **Professionelle Produktion** (besser: OBS)
- 📹 **Aufnahme-fokussiert** (besser: OBS)

---

## 🔗 Nützliche Links

- **WebRTC Dokumentation:** https://webrtc.org/
- **MDN WebRTC Guide:** https://developer.mozilla.org/en-US/docs/Web/API/WebRTC_API
- **WebRTC Samples:** https://webrtc.github.io/samples/
- **STUN/TURN Server:** https://github.com/coturn/coturn
- **Socket.io Alternative:** https://socket.io/

---

## ✅ Checkliste

- [ ] Docker-Container gestartet: `docker-compose up -d`
- [ ] Signaling-Server läuft: `docker-compose logs webrtc-signaling`
- [ ] stream.html geöffnet: `http://localhost:8080/stream.html`
- [ ] Kamera gestartet und Broadcast läuft
- [ ] Room-ID notiert
- [ ] viewer.html geöffnet: `http://localhost:8080/viewer.html`
- [ ] Stream verbunden und läuft ✅

---

**Viel Erfolg mit deinem Echtzeit-Streaming! ⚡🚀**
