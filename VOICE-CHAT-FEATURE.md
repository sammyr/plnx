# 🎤 Bidirektionale Sprachkommunikation

## 📋 Übersicht

Die Watch-Cam-Seite unterstützt jetzt **bidirektionale Audio-Kommunikation** zwischen Viewer und Streamer während einer aktiven Chat-Session.

## ✨ Features

### Für Viewer (watch-cam.php)

- **Automatische Mikrofon-Aktivierung**: Beim Öffnen des Chats wird das Mikrofon automatisch aktiviert
- **Mikrofon-Steuerung**: Toggle-Button im Chat-Header zum Ein-/Ausschalten
- **Visuelles Feedback**: 
  - 🎤 Grüner Button = Mikrofon aktiv
  - 🔇 Roter Button = Mikrofon aus
- **Echo-Cancellation**: Automatische Echo-Unterdrückung und Rauschunterdrückung
- **Automatische Deaktivierung**: Mikrofon wird beim Schließen des Chats automatisch deaktiviert

### Für Streamer (stream.php)

- Empfängt Audio vom Viewer über die bestehende WebRTC-Verbindung
- Keine zusätzliche Konfiguration erforderlich

## 🔧 Technische Details

### WebRTC Audio-Track

```javascript
// Viewer sendet Audio über WebRTC PeerConnection
localAudioStream = await navigator.mediaDevices.getUserMedia({
    audio: {
        echoCancellation: true,
        noiseSuppression: true,
        autoGainControl: true
    }
});
```

### Funktionsweise

1. **Initialisierung**: Beim Laden der Seite wird ein stummer Audio-Track zur PeerConnection hinzugefügt
2. **Chat-Start**: Beim Öffnen des Chats wird Mikrofon-Zugriff angefordert
3. **Track-Ersetzung**: Der stumme Track wird durch den echten Mikrofon-Track ersetzt
4. **Bidirektionale Kommunikation**: Viewer hört Streamer + Streamer hört Viewer
5. **Chat-Ende**: Mikrofon wird deaktiviert und Track wird wieder stumm geschaltet

### Audio-Verarbeitung

- **Echo Cancellation**: Verhindert Rückkopplungen
- **Noise Suppression**: Reduziert Hintergrundgeräusche
- **Auto Gain Control**: Automatische Lautstärke-Anpassung

## 🎯 Benutzer-Flow

### Viewer-Perspektive

1. Öffnet `/watch-cam.php?room=Driver-Berlin-001`
2. Sieht Live-Stream (nur Video, kein Audio)
3. Klickt "Book Now" und zahlt
4. Chat öffnet sich automatisch
5. Browser fragt nach Mikrofon-Berechtigung
6. Nach Erlaubnis: Sprachverbindung ist aktiv
7. Kann mit Streamer sprechen UND Streamer hören
8. Kann Mikrofon jederzeit mit Toggle-Button steuern

### Streamer-Perspektive

1. Startet Broadcast auf `/stream.php`
2. Sendet Video + Audio
3. Wenn Viewer den Chat öffnet:
   - Hört automatisch die Stimme des Viewers
   - Kann normal weiter sprechen
   - Bidirektionale Kommunikation aktiv

## 🔐 Sicherheit & Datenschutz

- **Mikrofon-Berechtigung**: Browser fragt explizit nach Erlaubnis
- **Nur während Chat**: Audio wird nur während aktiver Chat-Session übertragen
- **Automatische Deaktivierung**: Beim Verlassen wird Mikrofon sofort deaktiviert
- **Keine Aufzeichnung**: Audio wird nicht gespeichert (nur Live-Übertragung)

## 📱 Browser-Kompatibilität

| Browser | Unterstützung | Hinweise |
|---------|--------------|----------|
| Chrome | ✅ Vollständig | Empfohlen |
| Firefox | ✅ Vollständig | - |
| Safari | ✅ Vollständig | Mikrofon-Berechtigung erforderlich |
| Edge | ✅ Vollständig | Chromium-basiert |
| Opera | ✅ Vollständig | - |

## 🐛 Troubleshooting

### Problem: Mikrofon funktioniert nicht

**Lösung 1**: Browser-Berechtigungen prüfen
- Chrome: `chrome://settings/content/microphone`
- Firefox: `about:preferences#privacy`
- Safari: Systemeinstellungen → Sicherheit → Mikrofon

**Lösung 2**: HTTPS erforderlich
- WebRTC Audio funktioniert nur über HTTPS (oder localhost)
- Stelle sicher, dass die Seite über HTTPS geladen wird

### Problem: Echo/Rückkopplung

**Lösung**: 
- Echo Cancellation ist standardmäßig aktiviert
- Verwende Kopfhörer für beste Ergebnisse
- Reduziere Lautstärke am Gerät

### Problem: Schlechte Audio-Qualität

**Lösung**:
- Prüfe Internetverbindung (min. 1 Mbps Upload)
- Schließe andere Anwendungen, die Mikrofon nutzen
- Verwende externes Mikrofon statt eingebautes

## 🔄 Aktualisierung bestehender Streams

Für bestehende Streams ist **keine Aktualisierung erforderlich**. Die Funktion ist automatisch verfügbar für:

- Alle `/watch-cam.php` Seiten
- Alle WebRTC-basierte Kamera-Streams
- Funktioniert mit bestehenden Driver-Streams

## 📊 Performance

- **Zusätzliche Bandbreite**: ~50-100 kbps Upload (Viewer)
- **Latenz**: ~100-300ms (WebRTC-typisch)
- **CPU-Last**: Minimal (Browser-native Verarbeitung)

## 🎨 UI-Anpassungen

### Mikrofon-Button Styling

```javascript
// Aktiv (Grün)
background: linear-gradient(135deg, #10b981, #059669)

// Inaktiv (Rot)
background: rgba(239, 68, 68, 0.2)
```

### Hinweis-Banner

```html
🎤 Sprachverbindung aktiv - Du kannst mit dem Streamer sprechen
```

## 🚀 Zukünftige Erweiterungen

Mögliche Verbesserungen:

- [ ] Audio-Level-Anzeige (Visualisierung)
- [ ] Push-to-Talk Modus
- [ ] Audio-Aufnahme für Qualitätssicherung
- [ ] Mehrere Viewer gleichzeitig (Gruppen-Chat)
- [ ] Sprachwahl-Erkennung (Speech-to-Text)

---

**Status**: ✅ Implementiert und einsatzbereit
**Version**: 1.0
**Datum**: 2025-11-06
