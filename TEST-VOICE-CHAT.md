# 🧪 Test-Anleitung: Bidirektionale Sprachkommunikation

## ✅ Schritt-für-Schritt Test

### Vorbereitung

1. **Server starten**
   ```powershell
   docker-compose up -d
   ```

2. **Browser vorbereiten**
   - Verwende Chrome oder Firefox (empfohlen)
   - Stelle sicher, dass ein Mikrofon angeschlossen ist
   - Öffne die Browser-Konsole (F12) für Debugging

---

## 🎥 Test als Streamer

### 1. Broadcaster-Seite öffnen
```
http://localhost/stream.php
```

### 2. Stream starten
1. Wähle Kamera aus
2. Klicke "Start Camera"
3. Klicke "Start Broadcast"
4. **Wichtig**: Browser fragt nach Kamera + Mikrofon-Berechtigung → **Erlauben**
5. Stream läuft jetzt

### 3. Prüfe Mikrofon-Symbol
- In der Browser-Leiste sollte ein **Mikrofon-Symbol** 🎤 erscheinen
- Dies zeigt, dass der Browser Zugriff auf dein Mikrofon hat

---

## 👁️ Test als Viewer

### 1. Viewer-Seite öffnen (in neuem Tab/Fenster)
```
http://localhost/watch-cam.php?room=Driver-Berlin-001
```
(Ersetze `Driver-Berlin-001` mit deiner tatsächlichen Room-ID vom Broadcaster)

### 2. Stream ansehen
- Du siehst jetzt den Live-Stream
- **Kein Mikrofon-Symbol** in der Browser-Leiste (noch nicht)
- Das ist normal! Das Mikrofon wird erst beim Chat aktiviert

### 3. Chat öffnen
1. Klicke auf "Book Now" in der Sidebar
2. Klicke "Pay Now" (Demo-Zahlung)
3. Chat öffnet sich automatisch

### 4. Mikrofon-Berechtigung
- **Jetzt** fragt der Browser nach Mikrofon-Berechtigung
- Klicke **"Zulassen"** / **"Allow"**
- ⚠️ **WICHTIG**: Erst NACH dieser Erlaubnis erscheint das Mikrofon-Symbol 🎤

### 5. Prüfe Mikrofon-Status

**Im Chat solltest du sehen:**
```
✅ Mikrofon aktiv
```

**In der Browser-Leiste:**
- Mikrofon-Symbol 🎤 sollte jetzt erscheinen
- Klicke darauf → zeigt "localhost verwendet dein Mikrofon"

**Im Chat-Header:**
- Grüner Button mit 🎤 "Mikrofon aktiv"

---

## 🔍 Debugging

### Konsolen-Logs prüfen

Öffne die Browser-Konsole (F12) und suche nach:

```
[Mikrofon] 🎤 Aktiviere Mikrofon...
[Mikrofon] PeerConnection Status: Vorhanden
[Mikrofon] Fordere Mikrofon-Berechtigung an...
[Mikrofon] ✅ Mikrofon-Zugriff erhalten
[Mikrofon] Audio Tracks: 1
[Mikrofon] Audio Track: [Name deines Mikrofons] Enabled: true
[Mikrofon] ✅ Audio-Track ersetzt
[Mikrofon] 🎉 Mikrofon erfolgreich aktiviert!
```

### Häufige Probleme

#### ❌ Kein Mikrofon-Symbol erscheint

**Mögliche Ursachen:**

1. **Chat nicht geöffnet**
   - Lösung: Klicke "Book Now" → "Pay Now"

2. **Berechtigung verweigert**
   - Konsole zeigt: `NotAllowedError`
   - Lösung: Browser-Einstellungen → Mikrofon-Berechtigung für localhost erlauben

3. **Kein Mikrofon angeschlossen**
   - Konsole zeigt: `NotFoundError`
   - Lösung: Mikrofon anschließen und Seite neu laden

4. **HTTPS erforderlich**
   - WebRTC Audio funktioniert nur über HTTPS (oder localhost)
   - Auf localhost sollte es funktionieren

#### ❌ Mikrofon-Symbol erscheint, aber kein Audio

**Prüfe:**

1. **Mikrofon-Lautstärke**
   - Windows: Systemeinstellungen → Sound → Eingabegeräte
   - Stelle sicher, dass Mikrofon nicht stumm ist

2. **Richtiges Mikrofon ausgewählt**
   - Browser verwendet Standard-Mikrofon
   - Ändere Standard-Mikrofon in Windows-Einstellungen

3. **WebRTC-Verbindung**
   - Konsole: Prüfe `PeerConnection State: connected`

---

## 🎯 Erfolgreiche Test-Checkliste

### Streamer (stream.php)
- [ ] Mikrofon-Symbol 🎤 in Browser-Leiste sichtbar
- [ ] Stream läuft (Video sichtbar)
- [ ] Kann eigene Stimme im Raum hören (wenn Lautsprecher an)

### Viewer (watch-cam.php)
- [ ] Stream sichtbar (Video läuft)
- [ ] "Book Now" funktioniert
- [ ] Chat öffnet sich nach Zahlung
- [ ] Browser fragt nach Mikrofon-Berechtigung
- [ ] **Mikrofon-Symbol 🎤 erscheint NACH Erlaubnis**
- [ ] Grüner Button "🎤 Mikrofon aktiv" im Chat
- [ ] Konsole zeigt "Mikrofon erfolgreich aktiviert"

### Bidirektionale Kommunikation
- [ ] Viewer kann Streamer hören
- [ ] Streamer kann Viewer hören
- [ ] Kein Echo (Echo Cancellation funktioniert)
- [ ] Mikrofon-Toggle funktioniert (Ein/Aus)

---

## 🔧 Browser-Berechtigungen manuell prüfen

### Chrome
1. Klicke auf das **Schloss-Symbol** links neben der URL
2. Klicke auf "Berechtigungen für diese Website"
3. Prüfe **Mikrofon**: Sollte "Zulassen" sein
4. Falls "Blockiert" → Ändere auf "Zulassen" und lade Seite neu

### Firefox
1. Klicke auf das **Schloss-Symbol** links neben der URL
2. Klicke auf "Verbindung ist sicher" → "Weitere Informationen"
3. Tab "Berechtigungen"
4. Prüfe **Mikrofon verwenden**: Sollte "Erlauben" sein

---

## 📊 Erwartetes Verhalten

### Timeline

```
1. Seite laden (watch-cam.php)
   → Kein Mikrofon-Symbol (normal)

2. Chat öffnen (nach Bezahlung)
   → Browser fragt nach Berechtigung

3. Berechtigung erteilen
   → Mikrofon-Symbol erscheint 🎤
   → Chat zeigt "✅ Mikrofon aktiv"

4. Sprechen
   → Streamer hört dich
   → Du hörst Streamer

5. Chat schließen
   → Mikrofon-Symbol verschwindet
   → Mikrofon wird deaktiviert
```

---

## 🐛 Fehlersuche

### Konsolen-Befehle zum Testen

```javascript
// Prüfe ob Mikrofon-Funktion existiert
console.log(typeof window.enableViewerMicrophone);
// Sollte: "function"

// Prüfe aktuellen Mikrofon-Status
console.log(isMicEnabled);
// Sollte: true (wenn aktiv) oder false

// Manuell Mikrofon aktivieren
window.enableViewerMicrophone();

// Manuell Mikrofon deaktivieren
window.disableViewerMicrophone();
```

---

## ✅ Erfolg!

Wenn du das Mikrofon-Symbol 🎤 in der Browser-Leiste siehst UND der Chat "Mikrofon aktiv" anzeigt, funktioniert alles korrekt!

**Nächste Schritte:**
- Teste mit echtem Streamer und Viewer (2 Geräte)
- Prüfe Audio-Qualität
- Teste Echo Cancellation mit Lautsprechern

---

**Bei weiteren Problemen**: Konsolen-Logs kopieren und Fehler melden!
