# 🌐 Browser-basiertes Streaming - Anleitung

## ⚠️ Wichtiger Hinweis

**Browser können NICHT direkt zu RTMP streamen!** Browser unterstützen kein RTMP-Protokoll nativ.

Ich habe dir **zwei Lösungen** erstellt:

---

## 🎯 Lösung 1: Browser-zu-Browser (Lokal)

### Dateien:
- **`stream.html`** - Webcam aufnehmen und senden
- **`viewer.html`** - Stream empfangen und ansehen

### So funktioniert's:

1. **Öffne `stream.html` im Browser:**
   ```
   http://localhost:8080/stream.html
   ```

2. **Klicke auf "Kamera starten"** und erlaube den Zugriff

3. **Klicke auf "Broadcast starten"**
   - Du erhältst eine Stream-ID (z.B. `stream_abc123`)

4. **Öffne `viewer.html` in einem NEUEN TAB:**
   ```
   http://localhost:8080/viewer.html
   ```

5. **Im viewer.html:**
   - Gib die Stream-ID ein
   - Klicke "Stream empfangen"
   - ODER klicke "Auto-Connect" für automatische Verbindung

### ⚠️ Einschränkungen:
- ✅ Funktioniert nur im **gleichen Browser**
- ✅ Beide Tabs müssen **gleichzeitig offen** sein
- ❌ Funktioniert **NICHT** über verschiedene Geräte
- ❌ Keine echte RTMP-Übertragung

### Technische Details:
- Verwendet `localStorage` für Stream-Informationen
- `BroadcastChannel` API für Tab-Kommunikation
- MediaStream wird lokal gehalten

---

## 🚀 Lösung 2: OBS + SRS Server (Professionell)

### Für echtes Streaming über Netzwerk:

1. **Verwende OBS Studio** (kostenlos)
   - Download: https://obsproject.com/

2. **OBS konfigurieren:**
   ```
   Server:      rtmp://localhost:1935/live
   Stream-Key:  livestream
   ```

3. **Im Browser ansehen:**
   - Öffne: `http://localhost:8080/viewer.html`
   - Wechsle zum Tab "Server-Stream"
   - Klicke "HLS abspielen"

### ✅ Vorteile:
- ✅ Funktioniert über **Netzwerk**
- ✅ Mehrere Zuschauer gleichzeitig
- ✅ Professionelle Qualität
- ✅ Niedrige Latenz mit FLV
- ✅ Aufnahme möglich

---

## 📁 Datei-Übersicht

| Datei | Zweck |
|-------|-------|
| **stream.html** | Webcam senden (Browser-zu-Browser) |
| **viewer.html** | Stream empfangen (beide Modi) |
| **index.html** | Original SRS Player (HLS/FLV) |

---

## 🔧 Warum Browser kein RTMP können

### Technische Gründe:

1. **RTMP basiert auf TCP/Flash**
   - Flash ist seit 2020 tot
   - Browser unterstützen kein RTMP nativ

2. **Browser-Alternativen:**
   - **WebRTC** - Peer-to-Peer (kompliziert)
   - **HLS** - HTTP Live Streaming (Empfang)
   - **MSE** - Media Source Extensions (Empfang)
   - **MediaRecorder** - Lokale Aufnahme

3. **Für RTMP-Upload braucht man:**
   - Native App (OBS, FFmpeg)
   - Oder WebSocket-Server als Bridge
   - Oder WebRTC-zu-RTMP Gateway

---

## 💡 Empfehlung

### Für deine Anforderung:

**Wenn du Webcam über Browser streamen willst:**

1. **Einfach (lokal):**
   - Nutze `stream.html` + `viewer.html`
   - Beide im gleichen Browser öffnen

2. **Professionell (Netzwerk):**
   - Installiere OBS Studio
   - Streame zu SRS Server
   - Schaue mit `viewer.html` (Server-Stream Tab)

---

## 🎬 Schnellstart

### Browser-zu-Browser:
```
1. http://localhost:8080/stream.html  → Kamera starten → Broadcast starten
2. http://localhost:8080/viewer.html → Auto-Connect klicken
```

### OBS-zu-Browser:
```
1. OBS: rtmp://localhost:1935/live + Key: livestream
2. http://localhost:8080/viewer.html → Server-Stream Tab → HLS abspielen
```

---

## 🔍 Problemlösung

### "Stream wird nicht angezeigt" (Browser-zu-Browser)

**Checkliste:**
- [ ] Beide Tabs im **gleichen Browser**?
- [ ] `stream.html` zeigt "🔴 LIVE"?
- [ ] Stream-ID korrekt eingegeben?
- [ ] Kamera-Zugriff erlaubt?

### "Server-Stream nicht verfügbar"

**Checkliste:**
- [ ] SRS-Container läuft? → `docker-compose ps`
- [ ] OBS sendet Stream?
- [ ] URL korrekt? → `http://localhost:8080/live/livestream.m3u8`

---

## 📚 Weiterführende Infos

### Wenn du echtes Browser-zu-RTMP willst:

Du bräuchtest einen **WebSocket-zu-RTMP Bridge Server**:

1. **Node.js Server** der:
   - WebSocket empfängt (vom Browser)
   - Zu RTMP konvertiert (mit FFmpeg)
   - An SRS weiterleitet

2. **Beispiel-Projekte:**
   - `node-media-server`
   - `rtmp-server` npm package
   - Custom WebSocket + FFmpeg Bridge

**Aber:** Das ist komplex und OBS ist die bessere Lösung!

---

## ✅ Zusammenfassung

| Methode | Komplexität | Qualität | Netzwerk | Empfehlung |
|---------|-------------|----------|----------|------------|
| Browser-zu-Browser | ⭐ Einfach | ⭐⭐ OK | ❌ Nein | Nur zum Testen |
| OBS + SRS | ⭐⭐ Mittel | ⭐⭐⭐⭐⭐ Perfekt | ✅ Ja | **Empfohlen!** |
| WebSocket Bridge | ⭐⭐⭐⭐ Schwer | ⭐⭐⭐ Gut | ✅ Ja | Nur für Entwickler |

---

**Meine Empfehlung: Nutze OBS Studio + SRS Server für professionelles Streaming! 🚀**
