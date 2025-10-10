# 🚗 Stream-ID System - Driver-Stadt-Nummer

## 📋 Übersicht

Alle Streams werden automatisch nach diesem Schema benannt:

```
Driver-[Stadt]-[Nummer]
```

### Beispiele:
```
Driver-Berlin-001
Driver-Berlin-002
Driver-Berlin-003
Driver-Munich-001
Driver-Hamburg-001
Driver-Frankfurt-001
```

---

## 🌍 Wie funktioniert's?

### 1. **Stadt wird automatisch ermittelt**

Über IP-Geolocation (ipapi.co):
- Broadcaster startet Stream
- System ermittelt Stadt über IP
- Stadt wird in Stream-ID verwendet

### 2. **Nummer wird hochgezählt**

Für jede Stadt separat:
- Erster Stream in Berlin: `Driver-Berlin-001`
- Zweiter Stream in Berlin: `Driver-Berlin-002`
- Erster Stream in Munich: `Driver-Munich-001`

### 3. **Automatische Generierung**

Wenn Room-ID Feld **leer** bleibt:
- ✅ Stadt wird ermittelt
- ✅ Nächste Nummer wird berechnet
- ✅ Stream-ID wird generiert

Wenn Room-ID **manuell eingegeben**:
- ✅ Eigene ID wird verwendet
- ⚠️ Muss eindeutig sein!

---

## 🎯 Beispiel-Workflow

### Broadcaster 1 (in Berlin):
```
1. Kamera starten
2. Broadcast starten
3. System generiert: Driver-Berlin-001
```

### Broadcaster 2 (in Berlin):
```
1. Kamera starten
2. Broadcast starten
3. System generiert: Driver-Berlin-002
```

### Broadcaster 3 (in Munich):
```
1. Kamera starten
2. Broadcast starten
3. System generiert: Driver-Munich-001
```

---

## 🔧 Manuelle Stream-ID

Wenn du eine eigene ID verwenden möchtest:

1. Gib im Feld "Room-ID (optional)" ein:
   ```
   Driver-Custom-999
   ```

2. Klicke "Broadcast starten"

3. Diese ID wird verwendet (keine automatische Generierung)

---

## 📊 Stream-ID Format

### Aufbau:
```
Driver-[Stadt]-[Nummer]
  │      │       │
  │      │       └─ 3-stellige Nummer (001-999)
  │      └───────── Stadt (z.B. Berlin, Munich)
  └──────────────── Prefix (immer "Driver")
```

### Regeln:
- **Prefix:** Immer "Driver"
- **Stadt:** Automatisch ermittelt oder manuell
- **Nummer:** 3-stellig mit führenden Nullen (001, 002, ...)

---

## 🌐 Unterstützte Städte

Das System unterstützt **alle Städte weltweit**:

### Deutschland:
- Berlin
- Munich
- Hamburg
- Frankfurt
- Cologne
- Stuttgart
- Dusseldorf
- etc.

### International:
- London
- Paris
- NewYork
- Tokyo
- etc.

---

## 🔍 Stream-ID im Viewer

Im Viewer (`viewer.html`) werden Streams angezeigt als:

```
┌─────────────────────────┐
│  📹 [Thumbnail]         │
│  🔴 LIVE                │
├─────────────────────────┤
│  Driver-Berlin-001      │
│  👁️ 5 Zuschauer         │
└─────────────────────────┘
```

---

## 💡 Vorteile

### Für Broadcaster:
- ✅ Automatische ID-Generierung
- ✅ Keine Duplikate
- ✅ Übersichtliche Benennung
- ✅ Stadt-basierte Organisation

### Für Zuschauer:
- ✅ Erkennen wo Stream herkommt
- ✅ Sortierung nach Stadt möglich
- ✅ Professionelle Darstellung

---

## 🔧 Technische Details

### Stadt-Ermittlung:
```javascript
async function getCurrentCity() {
    const response = await fetch('https://ipapi.co/json/');
    const data = await response.json();
    return data.city || 'Unknown';
}
```

### Nummern-Berechnung:
```javascript
async function getNextStreamNumber(city) {
    // Hole alle Streams für diese Stadt
    const cityStreams = streams.filter(s => 
        s.startsWith(`Driver-${city}-`)
    );
    
    // Finde höchste Nummer
    const maxNumber = Math.max(...cityStreams.map(extractNumber));
    
    // Erhöhe um 1
    return maxNumber + 1;
}
```

---

## 📝 Beispiele

### Automatisch generiert:
```
Driver-Berlin-001
Driver-Berlin-002
Driver-Munich-001
Driver-Hamburg-001
Driver-Frankfurt-001
```

### Manuell eingegeben:
```
Driver-TestStream-001
Driver-Event-Special
Driver-Demo-999
```

---

## ⚙️ Konfiguration

### Standard-Stadt ändern:

In `stream.html` Zeile 442:
```javascript
return data.city || 'Unknown';
```

Ändern zu:
```javascript
return data.city || 'Berlin';  // Deine Standard-Stadt
```

### Prefix ändern:

In `stream.html` Zeile 504:
```javascript
roomId = `Driver-${city}-${number}`;
```

Ändern zu:
```javascript
roomId = `MyPrefix-${city}-${number}`;
```

---

## 🎉 Fertig!

Dein Stream-ID System ist jetzt aktiv!

**Starte einen Broadcast und die ID wird automatisch generiert:**
```
Driver-[DeineStadt]-001
```
