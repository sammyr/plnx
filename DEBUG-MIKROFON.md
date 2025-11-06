# 🐛 Debug-Anleitung: Mikrofon-Problem

## Problem
Der Chat zeigt "Mikrofon aus", aber es wird nicht nach Berechtigung gefragt.

## 🔍 Debug-Schritte

### 1. Browser-Konsole öffnen
Drücke **F12** und gehe zum **Console**-Tab

### 2. Prüfe ob Funktionen geladen sind

Führe in der Konsole aus:
```javascript
console.log('enableViewerMicrophone:', typeof window.enableViewerMicrophone);
console.log('disableViewerMicrophone:', typeof window.disableViewerMicrophone);
console.log('toggleViewerMicrophone:', typeof window.toggleViewerMicrophone);
```

**Erwartetes Ergebnis:**
```
enableViewerMicrophone: function
disableViewerMicrophone: function
toggleViewerMicrophone: function
```

**Falls "undefined":**
- Script wurde nicht geladen oder hat Fehler
- Prüfe Browser-Konsole auf JavaScript-Fehler

### 3. Teste Mikrofon manuell

Führe in der Konsole aus:
```javascript
window.enableViewerMicrophone()
```

**Was sollte passieren:**
1. Browser fragt nach Mikrofon-Berechtigung
2. Konsole zeigt: `[Mikrofon] 🎤 Aktiviere Mikrofon...`
3. Nach Erlaubnis: `[Mikrofon] ✅ Mikrofon-Zugriff erhalten`
4. Mikrofon-Symbol 🎤 erscheint in Browser-Leiste

### 4. Prüfe PeerConnection

```javascript
console.log('PeerConnection:', peerConnection);
console.log('Signaling Socket:', signalingSocket);
```

**Beide sollten existieren**, nicht `null` oder `undefined`

### 5. Prüfe Chat-Status

```javascript
const roomId = new URLSearchParams(window.location.search).get('room');
console.log('Room ID:', roomId);
console.log('Chat aktiv:', localStorage.getItem(`chatActive_${roomId}`));
```

## 🔧 Mögliche Lösungen

### Lösung 1: Seite neu laden
1. Öffne Chat (Book Now → Pay Now)
2. Drücke **F5** (Reload)
3. Chat sollte automatisch wieder öffnen
4. Mikrofon sollte aktiviert werden

### Lösung 2: Manuell aktivieren
1. Chat öffnen
2. Browser-Konsole öffnen (F12)
3. Eingeben: `window.enableViewerMicrophone()`
4. Enter drücken
5. Berechtigung erlauben

### Lösung 3: Browser-Cache leeren
1. **Strg + Shift + Delete**
2. "Cached Images and Files" auswählen
3. Löschen
4. Seite neu laden

### Lösung 4: Mikrofon-Button direkt klicken
1. Im Chat-Header ist ein Button "🔇 Mikrofon aus"
2. Klicke darauf
3. Browser sollte nach Berechtigung fragen

## 📊 Erwartete Konsolen-Ausgabe

Beim Öffnen des Chats solltest du sehen:

```
[openChatWindow] Funktion gestartet
[openChatWindow] Aktiviere Mikrofon für Sprachkommunikation...
[openChatWindow] window.enableViewerMicrophone: function
[openChatWindow] Rufe enableViewerMicrophone auf...
[Mikrofon] 🎤 Aktiviere Mikrofon...
[Mikrofon] PeerConnection Status: Vorhanden
[Mikrofon] Fordere Mikrofon-Berechtigung an...
[Mikrofon] ✅ Mikrofon-Zugriff erhalten
[Mikrofon] Audio Tracks: 1
[Mikrofon] Audio Track: [Dein Mikrofon] Enabled: true
[Mikrofon] ✅ Audio-Track ersetzt
[Mikrofon] 🎉 Mikrofon erfolgreich aktiviert!
[openChatWindow] Mikrofon-Aktivierung: Erfolgreich
```

## ❌ Häufige Fehler

### Fehler: "NotAllowedError"
```
[Mikrofon] ❌ Fehler beim Aktivieren: NotAllowedError
```
**Lösung:** Browser-Einstellungen → Mikrofon-Berechtigung für localhost erlauben

### Fehler: "NotFoundError"
```
[Mikrofon] ❌ Fehler beim Aktivieren: NotFoundError
```
**Lösung:** Kein Mikrofon gefunden - Mikrofon anschließen

### Fehler: "enableViewerMicrophone Funktion nicht gefunden"
```
[openChatWindow] enableViewerMicrophone Funktion nicht gefunden!
```
**Lösung:** 
1. Prüfe ob `watch-cam-webrtc.js` geladen wurde
2. Prüfe Browser-Konsole auf JavaScript-Fehler
3. Seite neu laden (F5)

## 🧪 Test-Befehl

Kopiere diesen kompletten Block in die Konsole:

```javascript
(async function testMicrophone() {
    console.log('=== MIKROFON TEST START ===');
    console.log('1. Funktionen vorhanden?');
    console.log('   enableViewerMicrophone:', typeof window.enableViewerMicrophone);
    console.log('   toggleViewerMicrophone:', typeof window.toggleViewerMicrophone);
    
    console.log('2. WebRTC Status:');
    console.log('   PeerConnection:', peerConnection ? 'OK' : 'FEHLT');
    console.log('   Signaling Socket:', signalingSocket ? 'OK' : 'FEHLT');
    
    console.log('3. Versuche Mikrofon zu aktivieren...');
    if (typeof window.enableViewerMicrophone === 'function') {
        try {
            const result = await window.enableViewerMicrophone();
            console.log('   ✅ Erfolgreich:', result);
        } catch (error) {
            console.error('   ❌ Fehler:', error);
        }
    } else {
        console.error('   ❌ Funktion nicht gefunden!');
    }
    console.log('=== MIKROFON TEST ENDE ===');
})();
```

## 📝 Ergebnis melden

Wenn das Problem weiterhin besteht, kopiere die komplette Konsolen-Ausgabe und sende sie mir.

**Wichtige Informationen:**
- Browser und Version
- Komplette Konsolen-Ausgabe
- Fehlermeldungen
- Welche Schritte wurden durchgeführt
