# 🔍 Debug-Anleitung - Stream wird nicht angezeigt

## Problem
Du kannst auf das Thumbnail klicken und den Raum betreten, aber der Stream wird nicht angezeigt.

## Lösung - Schritt für Schritt

### 1. Browser-Konsole öffnen
Drücke **F12** oder **Rechtsklick → Untersuchen**

### 2. Broadcaster starten (stream.html)

1. Öffne: `http://localhost:8080/stream.html`
2. Öffne Browser-Konsole (F12)
3. Klicke "Kamera starten"
4. Klicke "Broadcast starten"

**In der Konsole solltest du sehen:**
```
✅ Verbunden mit Signaling-Server
Broadcaster registriert
```

### 3. Viewer öffnen (viewer.html)

1. Öffne in **neuem Tab**: `http://localhost:8080/viewer.html`
2. Öffne Browser-Konsole (F12)
3. Du solltest Stream-Karten mit Video-Thumbnails sehen
4. Klicke auf eine Stream-Karte
5. Klicke "Beitreten" im Popover

**In der Konsole solltest du sehen:**
```
Offer empfangen: {...}
Stream empfangen! MediaStream {...}
Connection state: connected
ICE connection state: connected
```

### 4. Wenn der Stream NICHT angezeigt wird:

#### Prüfe in der Broadcaster-Konsole:
```
Neuer Viewer: viewer_xxxxx
Track hinzugefügt: video
Track hinzugefügt: audio
Offer erstellt und gesendet
Broadcaster Connection state: connected
```

#### Prüfe in der Viewer-Konsole:
```
Offer empfangen: {...}
Stream empfangen! MediaStream {...}
Connection state: connecting → connected
```

### 5. Häufige Probleme:

#### Problem: "Kein Offer empfangen"
**Lösung:**
- Broadcaster muss ZUERST gestartet werden
- Dann erst Viewer öffnen

#### Problem: "Connection state: failed"
**Lösung:**
- Firewall blockiert WebRTC
- Beide Tabs im gleichen Browser öffnen
- Localhost verwenden (nicht 127.0.0.1)

#### Problem: "Stream empfangen aber Video schwarz"
**Lösung:**
- Kamera-Berechtigung prüfen
- Andere Apps schließen (die Kamera verwenden)
- Browser neu starten

### 6. Manueller Test:

#### Broadcaster-Konsole:
```javascript
// Prüfe ob Stream läuft
console.log('Local Stream:', localStream);
console.log('Tracks:', localStream.getTracks());
console.log('Video Track enabled:', localStream.getVideoTracks()[0].enabled);
```

#### Viewer-Konsole:
```javascript
// Prüfe ob Stream empfangen wurde
console.log('Remote Video srcObject:', remoteVideo.srcObject);
console.log('Peer Connection:', peerConnection);
console.log('Connection State:', peerConnection.connectionState);
```

### 7. Neustart-Prozedur:

```powershell
# 1. Docker neu starten
docker-compose restart

# 2. Browser-Cache leeren
Strg + Shift + Delete → Cache leeren

# 3. Broadcaster öffnen
http://localhost:8080/stream.html

# 4. Kamera starten → Broadcast starten

# 5. Viewer öffnen (neuer Tab)
http://localhost:8080/viewer.html

# 6. Stream-Karte klicken → Beitreten
```

### 8. Signaling-Server Logs prüfen:

```powershell
docker-compose logs -f webrtc-signaling
```

**Du solltest sehen:**
```
📱 Neue Verbindung
📡 Broadcaster registriert: room_xxxxx
👁️ Viewer verbunden: room_xxxxx (1 Viewer)
📤 Offer gesendet: Broadcaster → Viewer viewer_xxxxx
📥 Answer gesendet: Viewer viewer_xxxxx → Broadcaster
```

### 9. Erfolgreicher Stream-Ablauf:

1. ✅ Broadcaster: Kamera gestartet
2. ✅ Broadcaster: Broadcast gestartet
3. ✅ Broadcaster: Mit Signaling-Server verbunden
4. ✅ Viewer: Mit Signaling-Server verbunden
5. ✅ Viewer: Stream-Karte sichtbar
6. ✅ Viewer: Popover öffnet sich
7. ✅ Viewer: "Beitreten" geklickt
8. ✅ Signaling: Offer gesendet
9. ✅ Signaling: Answer empfangen
10. ✅ WebRTC: Peer Connection established
11. ✅ Viewer: Stream wird angezeigt! 🎉

### 10. Schnell-Check:

**Broadcaster (stream.html):**
- [ ] Kamera-Zugriff erlaubt?
- [ ] Video im Preview sichtbar?
- [ ] "LIVE" Badge rot?
- [ ] Verbindung zum Server: Grün?

**Viewer (viewer.html):**
- [ ] Stream-Karte sichtbar?
- [ ] Video-Thumbnail läuft?
- [ ] Popover öffnet sich?
- [ ] Nach "Beitreten": Vollbild-Ansicht?
- [ ] Video wird abgespielt?

---

## Wenn alles nicht hilft:

### Kompletter Neustart:

```powershell
# 1. Container stoppen
docker-compose down

# 2. Container starten
docker-compose up -d

# 3. Warte 5 Sekunden

# 4. Browser komplett schließen

# 5. Browser neu öffnen

# 6. Broadcaster öffnen (Inkognito-Modus)
http://localhost:8080/stream.html

# 7. Viewer öffnen (neuer Tab, Inkognito)
http://localhost:8080/viewer.html
```

---

## Debug-Befehle für Browser-Konsole:

### Broadcaster:
```javascript
// Stream-Info
console.log('Broadcasting:', isBroadcasting);
console.log('Room ID:', roomId);
console.log('Peer Connections:', peerConnections.size);
console.log('Local Stream Tracks:', localStream?.getTracks());

// Verbindungs-Status
peerConnections.forEach((pc, peerId) => {
    console.log(`Peer ${peerId}:`, pc.connectionState);
});
```

### Viewer:
```javascript
// Verbindungs-Info
console.log('Current Room:', currentRoomId);
console.log('Peer Connection:', peerConnection?.connectionState);
console.log('ICE State:', peerConnection?.iceConnectionState);
console.log('Remote Stream:', remoteVideo.srcObject);

// Stream-Tracks
if (remoteVideo.srcObject) {
    console.log('Tracks:', remoteVideo.srcObject.getTracks());
}
```

---

**Bei weiteren Problemen: Schicke mir die Browser-Konsolen-Logs! 🔍**
