# Klickbereich-Fix Dokumentation

## Problem
Die Kacheln in viewer.php hatten inkonsistente Klickbereiche, die zu Mehrfachklicks und falschen Video-Links führten.

## Finale Lösung

### 1. PHP-generierte Demo-Videos (viewer.php Zeile 568-593)
```html
<div class="stream-card">
    <a href="watch.php?room=<?php echo urlencode($video['id']); ?>" target="_blank">
        <div class="stream-thumbnail">
            <video pointer-events:none z-index:0>
            <div class="live-badge" pointer-events:none z-index:1>
        </div>
        <div class="stream-info">...</div>
    </a>
</div>
```

### 2. JavaScript-generierte Live-Streams (viewer.php Zeile 928-963)
```javascript
card.innerHTML = `
    <a href="${linkUrl}" target="_blank">
        <div class="stream-thumbnail">
            <video/img z-index:0 pointer-events:none>
            <div class="live-badge" z-index:1>
        </div>
        <div class="stream-info">...</div>
    </a>
`;
thumbVideo.style.pointerEvents = 'none';
```

### 3. CSS-Fixes (viewer.php Zeile 97-123)
```css
.stream-card {
    position: relative;        /* Für ::before Positionierung */
    isolation: isolate;        /* Verhindert z-index Konflikte */
}

.stream-card::before {
    position: absolute;
    pointer-events: none;      /* Gradient blockiert nicht */
}
```

## Wichtige Regeln

### ✅ DO:
- **Immer `<a href>` verwenden** statt onclick/addEventListener
- **pointer-events: none** auf Video und Badge
- **z-index: 0** für Video (Hintergrund)
- **z-index: 1** für Badge (darüber)
- **position: relative** auf .stream-card
- **isolation: isolate** auf .stream-card

### ❌ DON'T:
- **Kein pointer-events: none** auf Container (.stream-thumbnail)
- **Kein z-index: -1** (verschwindet hinter Hintergrund)
- **Kein onclick** auf div-Elementen
- **Keine addEventListener** für einfache Links
- **Kein position: absolute** ohne position: relative auf Parent

## Warum es funktioniert

1. **`<a href>`**: Browser-native Links können nicht verwechselt werden
2. **pointer-events: none**: Video/Badge leiten Klicks zum Link durch
3. **z-index Layering**: Video im Hintergrund, Badge darüber, beide nicht klickbar
4. **isolation: isolate**: Jede Karte hat eigenen Stacking-Context
5. **position: relative**: ::before bleibt innerhalb der Karte

## Testen

```bash
# Server starten
.\start.ps1

# Browser öffnen
http://localhost:8080/viewer.php

# Prüfen:
- Alle Thumbnails sichtbar ✓
- Ein Klick öffnet korrektes Video ✓
- Hover zeigt URL unten links ✓
- Rechtsklick → "Link in neuem Tab" funktioniert ✓
- Obere und untere Karten beide klickbar ✓
```

## Dateien

- **viewer.php**: Hauptdatei mit PHP-Videos und JavaScript-Live-Streams
- **watch.php**: Zielseite mit PHP-Video-Mapping
- **Beide verwenden**: Einheitliche `<a href>` Struktur

## Letzte Änderung
2025-10-05 22:33 - Finale stabile Version
