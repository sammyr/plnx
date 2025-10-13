<?php
// Video-Konfiguration (serverseitig)
$demoVideos = [
    ['id' => 'demo_video_stream', 'title' => 'Die Totale Erinnerung', 'file' => 'ttr.m4v', 'thumbnail' => 'ttr_preview.mp4'],
    ['id' => 'demo_horrible_boss_stream', 'title' => 'Horrible Boss', 'file' => 'Horrible-Boss.m4v', 'thumbnail' => 'Horrible-Boss.m4v'],
    ['id' => 'demo_stingray_stream', 'title' => 'Stingray - Intro', 'file' => 'StingrayIntro.mp4', 'thumbnail' => 'StingrayIntro.mp4']
];

// API-Endpunkt für Video-Liste
if (isset($_GET['api']) && $_GET['api'] === 'videos') {
    header('Content-Type: application/json');
    echo json_encode(['videos' => $demoVideos]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Streams</title>
    <?php include 'components/head-meta.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@200;300;400;500;600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="components/global.css">
</head>
<body>
    <?php include 'components/header.php'; ?>
    <!-- Streams Overview -->
    <div id="streamsOverview">


        <div class="container">
            <div id="streamsGrid" class="streams-grid">
                <?php foreach ($demoVideos as $index => $video): ?>
                <div class="stream-card">
                    <a href="watch-movie.php?room=<?php echo urlencode($video['id']); ?>" style="text-decoration: none; color: inherit; display: block; width: 100%; height: 100%;">
                        <div class="stream-thumbnail" style="position: relative; overflow: hidden;">
                            <video class="thumbnail-video" autoplay muted loop playsinline style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 0; pointer-events: none;">
                                <source src="videos/<?php echo htmlspecialchars($video['thumbnail']); ?>" type="video/mp4">
                            </video>
                            <!-- Kein LIVE-Badge für Demo-Videos -->
                        </div>
                        <div class="stream-info">
                            <h3 class="stream-title"><?php echo htmlspecialchars($video['title']); ?></h3>
                            <div class="stream-meta">
                                <div class="stream-viewers">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <span>0 Zuschauer</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>

            <div id="emptyState" class="empty-state" style="display: none;">
                <div class="empty-icon">📡</div>
                <h3>Keine aktiven Streams</h3>
                <p>Warte auf Broadcasts...</p>
            </div>
        </div>
    </div>

    <!-- Stream View -->
    <div id="streamView" class="stream-view">
        <div class="stream-header">
            <div class="stream-room-id" id="currentRoomId">-</div>
            <button class="btn-back" onclick="backToOverview()">← Zurück</button>
        </div>
        <div class="stream-player">
            <video id="remoteVideo" playsinline controls preload="auto"></video>
            <div class="player-overlay">
                <div id="playerBadge" class="player-badge">Verbinde...</div>
            </div>
            <div id="manualPlayBtn" style="display: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 10;">
                <button onclick="document.getElementById('remoteVideo').play()" style="padding: 20px 40px; font-size: 20px; background: #6366f1; color: white; border: none; border-radius: 12px; cursor: pointer;">
                    ▶️ Play
                </button>
            </div>
        </div>
    </div>

    <!-- Popover -->
    <div id="popoverOverlay" class="popover-overlay">
        <div class="popover">
            <h2>Stream beitreten</h2>
            <p class="popover-subtitle">Möchtest du diesem Stream beitreten?</p>
            <div class="popover-room">
                <div class="popover-room-id" id="popoverRoomId">-</div>
            </div>
            <div class="popover-buttons">
                <button class="btn btn-secondary" onclick="closePopover()">Abbrechen</button>
                <button class="btn btn-primary" onclick="joinStream()">Beitreten</button>
            </div>
        </div>
    </div>

    <script>
        const SIGNALING_SERVER = window.location.protocol === 'https:' ? 'wss://ws.sammyrichter.de' : 'ws://localhost:3000';
        const ICE_SERVERS = [
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' }
        ];

        let signalingSocket = null;
        let peerConnection = null;
        let currentRoomId = null;
        let selectedRoomId = null;

        const streamsGrid = document.getElementById('streamsGrid');
        const emptyState = document.getElementById('emptyState');
        const streamsOverview = document.getElementById('streamsOverview');
        const streamView = document.getElementById('streamView');
        const popoverOverlay = document.getElementById('popoverOverlay');
        const remoteVideo = document.getElementById('remoteVideo');

        async function init() {
            await connectToSignalingServer();
            loadStreams();
            setInterval(loadStreams, 3000);
        }

        function connectToSignalingServer() {
            return new Promise((resolve, reject) => {
                if (signalingSocket && signalingSocket.readyState === WebSocket.OPEN) {
                    resolve();
                    return;
                }

                signalingSocket = new WebSocket(SIGNALING_SERVER);
                signalingSocket.onopen = () => {
                    console.log('[WebSocket] Verbunden mit Signaling-Server');
                    resolve();
                };
                signalingSocket.onmessage = handleSignalingMessage;
                signalingSocket.onerror = (error) => {
                    console.error('[WebSocket] Fehler:', error);
                    reject(error);
                };
                signalingSocket.onclose = () => {
                    console.log('[WebSocket] Verbindung geschlossen, versuche Reconnect...');
                    setTimeout(connectToSignalingServer, 2000);
                };
            });
        }

        async function handleSignalingMessage(event) {
            const data = JSON.parse(event.data);

            switch (data.type) {
                case 'rooms-list':
                    console.log('[WebSocket] rooms-list empfangen:', data.rooms);
                    displayRooms(data.rooms);
                    break;
                case 'offer':
                    // Prüfe ob es für Vorschau oder Haupt-Stream ist
                    if (data.preview) {
                        await handlePreviewOffer(data.roomId, data.offer);
                    } else {
                        await handleOffer(data.offer);
                    }
                    break;
                case 'ice-candidate':
                    if (data.preview && data.roomId) {
                        const pc = previewConnections.get(data.roomId);
                        if (pc) {
                            try {
                                await pc.addIceCandidate(new RTCIceCandidate(data.candidate));
                            } catch (e) {
                                console.warn('[Preview] ICE addCandidate Fehler für', data.roomId, e);
                            }
                        }
                    } else {
                        await handleIceCandidate(data.candidate);
                    }
                    break;
                case 'broadcaster-left':
                    backToOverview();
                    break;
            }
        }

        function loadStreams() {
            console.log('[loadStreams] Lade Streams...');
            if (signalingSocket && signalingSocket.readyState === WebSocket.OPEN) {
                console.log('[loadStreams] Sende get-rooms Request');
                signalingSocket.send(JSON.stringify({ type: 'get-rooms' }));
            } else {
                console.warn('[loadStreams] WebSocket nicht verbunden, Status:', signalingSocket ? signalingSocket.readyState : 'null');
            }
        }

        function getThumbnailSource(roomId) {
            // Demo-Videos bekommen eigene Clips
            if (roomId === 'demo_video_stream') {
                return { src: '/videos/ttr_preview.mp4', type: 'video/mp4', isVideo: true };
            }
            if (roomId === 'demo_stingray_stream') {
                return { src: '/videos/StingrayIntro.mp4', type: 'video/mp4', isVideo: true };
            }
            if (roomId === 'demo_subway_stream') {
                return { src: '/videos/Subway.m4v', type: 'video/mp4', isVideo: true };
            }
            if (roomId === 'demo_chuck_stream') {
                return { src: '/videos/Chuck.und.Larry.m4v', type: 'video/mp4', isVideo: true };
            }
            // Live-Räume: Screenshot-Thumbnail (JPG, aktualisiert alle 1 Sekunde)
            return { src: `/thumbnails/${roomId}.jpg?t=${Date.now()}`, type: 'image/jpeg', isVideo: false };
        }

        function createThumbnailVideo(roomId) {
            const source = getThumbnailSource(roomId);
            
            if (source.isVideo) {
                // Video-Thumbnail (Demo) - Optimiert für Performance
                const videoEl = document.createElement('video');
                videoEl.className = 'thumbnail-video';
                videoEl.autoplay = false; // Erst bei Sichtbarkeit
                videoEl.loop = true;
                videoEl.muted = true;
                videoEl.playsInline = true;
                videoEl.preload = 'none'; // Lade nichts bis nötig
                videoEl.loading = 'lazy'; // Lazy Loading

                const sourceEl = document.createElement('source');
                sourceEl.src = source.src;
                sourceEl.type = source.type;
                videoEl.appendChild(sourceEl);

                videoEl.addEventListener('error', () => {
                    if (videoEl.parentElement) videoEl.parentElement.removeChild(videoEl);
                }, { once: true });

                // Lazy Loading: Lade Video nur wenn sichtbar
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            videoEl.preload = 'metadata';
                            videoEl.load();
                            // Spiele ab wenn im Viewport
                            setTimeout(() => {
                                videoEl.play().catch(() => {});
                            }, 100);
                            observer.disconnect();
                        }
                    });
                }, { rootMargin: '50px' }); // Lade 50px bevor sichtbar
                
                observer.observe(videoEl);
                
                // Hover-Effekt: Stelle sicher dass Video spielt
                videoEl.addEventListener('mouseenter', () => {
                    if (videoEl.paused) {
                        requestAnimationFrame(() => videoEl.play().catch(() => {}));
                    }
                });

                return videoEl;
            } else {
                // Bild-Thumbnail (Live-Stream, robuste Fallbacks + optionale Slideshow)
                const imgEl = document.createElement('img');
                imgEl.className = 'thumbnail-video';
                imgEl.style.width = '100%';
                imgEl.style.height = '100%';
                imgEl.style.objectFit = 'cover';
                imgEl.style.display = 'block';
                imgEl.alt = roomId;

                let currentSlide = 1;
                const maxSlides = 30; // 3 Sekunden bei 10 FPS
                let availableSlides = 0;
                let slideshowStarted = false;
                let slideshowInterval = null;
                let checkInterval = null;

                // Fallback: Haupt-JPG zuerst versuchen, dann _1
                const tryPrimary = () => {
                    const primary = `/thumbnails/${roomId}.jpg?t=${Date.now()}`;
                    imgEl.src = primary;
                };

                imgEl.onerror = () => {
                    // Primärbild fehlgeschlagen: versuche _1
                    if (!imgEl.dataset.fallbackTried) {
                        imgEl.dataset.fallbackTried = '1';
                        imgEl.src = `/thumbnails/${roomId}_1.jpg?t=${Date.now()}`;
                        return;
                    }
                    // Auch _1 fehlgeschlagen: Bild ausblenden, Placeholder sichtbar lassen
                    imgEl.style.display = 'none';
                };

                imgEl.onload = () => {
                    imgEl.style.display = 'block';
                };

                // Starte Periodik zur Erkennung verfügbarer Slides
                const startCheck = () => {
                    checkInterval = setInterval(() => {
                        const headPromises = [];
                        for (let i = 1; i <= maxSlides; i++) {
                            headPromises.push(
                                fetch(`/thumbnails/${roomId}_${i}.jpg`, { method: 'HEAD' })
                                    .then(res => (res.ok ? 1 : 0))
                                    .catch(() => 0)
                            );
                        }

                        Promise.all(headPromises).then(results => {
                            availableSlides = results.reduce((sum, v) => sum + v, 0);
                            console.log(`[Slideshow] ${availableSlides}/${maxSlides} Bilder verfügbar`);

                            // Starte Slideshow ab mind. 2 Bildern (sanfter)
                            if (availableSlides >= 2 && !slideshowStarted) {
                                slideshowStarted = true;
                                clearInterval(checkInterval);
                                startSlideshow();
                            }
                        });
                    }, 2000);
                };

                const startSlideshow = () => {
                    console.log(`[Slideshow] Starte Animation mit ${availableSlides} Bildern (1 FPS)`);
                    slideshowInterval = setInterval(() => {
                        // Falls Anzahl sich später ändert, modulo begrenzen
                        if (availableSlides <= 1) return;
                        currentSlide = (currentSlide % availableSlides) + 1;
                        imgEl.src = `/thumbnails/${roomId}_${currentSlide}.jpg?t=${Date.now()}`;
                    }, 1000);
                };

                // Initial versuchen
                tryPrimary();
                startCheck();

                return imgEl;
            }
        }

        // Geolocation-Modul (OpenStreetMap Nominatim)
        const GeoLocationModule = {
            cache: new Map(),
            
            async reverseGeocode(lat, lon) {
                const cacheKey = `${lat.toFixed(4)},${lon.toFixed(4)}`;
                
                // Cache prüfen
                if (this.cache.has(cacheKey)) {
                    return this.cache.get(cacheKey);
                }
                
                try {
                    const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=18&addressdetails=1`;
                    const response = await fetch(url, {
                        headers: {
                            'User-Agent': 'LiveStreamViewer/1.0'
                        }
                    });
                    
                    if (!response.ok) throw new Error('Nominatim API Fehler');
                    
                    const data = await response.json();
                    const address = data.address || {};
                    
                    const result = {
                        city: address.city || address.town || address.village || 'Unbekannt',
                        district: address.suburb || address.neighbourhood || address.quarter || 'Zentrum',
                        country: address.country || ''
                    };
                    
                    // Cache speichern
                    this.cache.set(cacheKey, result);
                    console.log('📍 Geolocation aufgelöst:', result);
                    
                    return result;
                } catch (error) {
                    console.warn('Geolocation-Fehler:', error);
                    return { city: 'Unbekannt', district: 'Unbekannt', country: '' };
                }
            }
        };

        // Lade Locations aus JSON-Datei (synchron beim Start)
        let demoLocations = {};
        let locationsLoaded = false;
        
        // Lade Locations sofort (mit Cache-Busting)
        (async function loadLocations() {
            try {
                const ts = Date.now();
                const response = await fetch(`data/locations.json?t=${ts}`, { cache: 'no-store' });
                if (!response.ok) throw new Error('HTTP ' + response.status);
                demoLocations = await response.json();
                locationsLoaded = true;
                console.log('[Locations] Geladen aus locations.json:', Object.keys(demoLocations).length, 'Einträge');
                // Force re-render mit neuen Locations
                lastRoomsJson = '';
                if (cachedRooms && cachedRooms.length) {
                    console.log('[Locations] Re-render rooms mit frischen Locations');
                    await displayRooms(cachedRooms);
                }
            } catch (error) {
                console.error('[Locations] Fehler beim Laden:', error);
                // Fallback zu hardcoded Locations (mit Berlin/Mitte für 001)
                demoLocations = {
                    'Driver-Berlin-001': { 
                        city: 'Berlin', 
                        district: 'Mitte',
                        address: 'Mitte, Friedrichstraße 123', 
                        lat: 52.5200, 
                        lon: 13.3889 
                    },
                    'Driver-Berlin-002': { 
                        city: 'Berlin',
                        district: 'Kreuzberg',
                        address: 'Kreuzberg, Oranienstraße 45', 
                        lat: 52.4995, 
                        lon: 13.4197 
                    },
                    'Driver-Hamburg-001': { 
                        city: 'Hamburg',
                        district: 'St. Pauli',
                        address: 'St. Pauli, Reeperbahn 154', 
                        lat: 53.5511, 
                        lon: 9.9937 
                    },
                    'Driver-Munich-001': { 
                        city: 'München',
                        district: 'Schwabing',
                        address: 'Schwabing, Leopoldstraße 89', 
                        lat: 48.1351, 
                        lon: 11.5820 
                    }
                };
                locationsLoaded = true;
                lastRoomsJson = '';
                if (cachedRooms && cachedRooms.length) {
                    console.log('[Locations] Re-render rooms (Fallback)');
                    await displayRooms(cachedRooms);
                }
            }
        })();
        
        // Hilfsfunktionen
        async function parseLocation(roomId, locationData = null) {
            let coords = null;
            let isReal = false;
            let label = null;

            // Vorrang: Daten aus locations.json überschreiben immer eingehende Room-Daten
            const key = (roomId || '').trim();
            if (demoLocations[key]) {
                const demo = demoLocations[key];
                const locationLabel = demo.city && demo.district
                    ? `${demo.city} / ${demo.district}`
                    : (demo.city || 'Unbekannt');
                return {
                    label: locationLabel,
                    lat: demo.lat ?? null,
                    lon: demo.lon ?? null,
                    isReal: false
                };
            }

            if (locationData) {
                let normalized = locationData;

                if (typeof normalized === 'string') {
                    try {
                        normalized = JSON.parse(normalized);
                    } catch (error) {
                        // String ohne JSON -> direkt als Label verwenden
                        return {
                            label: normalized,
                            lat: null,
                            lon: null,
                            isReal: false
                        };
                    }
                }

                if (normalized && typeof normalized === 'object') {
                    if (normalized.lat !== undefined && normalized.lon !== undefined) {
                        coords = {
                            lat: Number(normalized.lat),
                            lon: Number(normalized.lon)
                        };
                        isReal = true;
                    }

                    if (normalized.address) {
                        label = normalized.address;
                    }

                    if (coords) {
                        if (!label) {
                            const geo = await GeoLocationModule.reverseGeocode(coords.lat, coords.lon);
                            label = `${geo.city} / ${geo.district}`;
                        }

                        return {
                            label,
                            lat: coords.lat,
                            lon: coords.lon,
                            isReal
                        };
                    }

                    if (label) {
                        return {
                            label,
                            lat: null,
                            lon: null,
                            isReal
                        };
                    }
                }
            }

            if (demoLocations[roomId]) {
                const demo = demoLocations[roomId];
                // Format: Stadt / Bezirk (NUR für viewer.php)
                const locationLabel = demo.city && demo.district 
                    ? `${demo.city} / ${demo.district}` 
                    : (demo.city || 'Unbekannt');
                return {
                    label: locationLabel,
                    lat: demo.lat,
                    lon: demo.lon,
                    isReal: false
                };
            }

            const parts = roomId.split('-');
            if (parts.length >= 2) {
                const city = parts[1];
                const districts = { 'Berlin': 'Charlottenburg', 'Munich': 'Schwabing', 'Hamburg': 'Altona' };
                const district = districts[city] || 'Zentrum';
                return {
                    label: `${city} / ${district}`,
                    lat: null,
                    lon: null,
                    isReal: false
                };
            }

            return {
                label: 'Unbekannt',
                lat: null,
                lon: null,
                isReal: false
            };
        }
        
        function formatDuration(ms) {
            const totalSeconds = Math.max(0, Math.floor(ms / 1000));
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;
            const pad = (value) => String(value).padStart(2, '0');

            const timeString = hours > 0
                ? `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`
                : `${pad(minutes)}:${pad(seconds)}`;

            return `Online seit: ${timeString}`;
        }
        
        let lastRoomsJson = '';
        let durationIntervals = [];
        let cachedRooms = [];
        
        async function displayRooms(rooms) {
            cachedRooms = rooms;
            // Warte bis Locations geladen sind
            while (!locationsLoaded) {
                await new Promise(resolve => setTimeout(resolve, 50));
            }
            
            // Verhindere unnötige Neuinitialisierung wenn sich nichts geändert hat
            const currentRoomsJson = JSON.stringify(rooms.map(r => r.roomId).sort());
            if (currentRoomsJson === lastRoomsJson) {
                console.log('[displayRooms] Keine Änderung, überspringe Neuinitialisierung');
                return;
            }
            lastRoomsJson = currentRoomsJson;
            
            // Stoppe alte Intervalle
            durationIntervals.forEach(interval => clearInterval(interval));
            durationIntervals = [];
            
            console.log('[displayRooms] Räume haben sich geändert, neu initialisieren');
            console.log('[displayRooms] Anzahl Räume:', rooms.length);
            console.log('[displayRooms] Räume:', rooms);
            // Demo-Videos sind bereits in PHP gerendert, nicht löschen!
            // Nur Live-Streams dynamisch hinzufügen

            // WebRTC Streams hinzufügen
            if (rooms.length > 0) {
                emptyState.style.display = 'none';
                streamsGrid.style.display = 'grid';

                // Verwende for...of statt forEach für async/await
                for (const room of rooms) {
                    try {
                        console.log('[displayRooms] Verarbeite Raum:', room.roomId);
                        const card = document.createElement('div');
                        card.className = 'stream-card';
                        
                        // Daten vorbereiten
                        const currentRoomId = room.roomId;
                        const currentLocation = room.location;
                        // "RESERVIERT" wird vom Server geliefert (für alle Besucher sichtbar)
                        const isReserved = room.reserved === true;
                        console.log(`[Reserve Check] ${currentRoomId}: Server reserved =`, room.reserved, 'isReserved =', isReserved);
                        
                        const locationInfo = await parseLocation(currentRoomId, currentLocation);
                        const duration = formatDuration(Date.now() - new Date(room.created).getTime());

                    // Erstelle Link (immer klickbar). Reserviert blendet nur das Overlay ein.
                    const linkUrl = `watch-cam.php?room=${encodeURIComponent(currentRoomId)}`;
                    const linkTarget = ''; // Kein target="_blank" - öffnet im selben Tab

                    const hasCoords = locationInfo && locationInfo.lat !== null && locationInfo.lon !== null;
                    const coordText = (() => {
                        if (!hasCoords) return '';
                        const formatCoord = (value, positiveDir, negativeDir) => {
                            const direction = value >= 0 ? positiveDir : negativeDir;
                            return `${Math.abs(value).toFixed(4)}° ${direction}`;
                        };
                        const latFormatted = formatCoord(locationInfo.lat, 'N', 'S');
                        const lonFormatted = formatCoord(locationInfo.lon, 'E', 'W');
                        return `${latFormatted}, ${lonFormatted}`;
                    })();

                    const locationBadgeMarkup = (() => {
                        if (!locationInfo || !locationInfo.label) return '';
                        const badgeTitle = locationInfo.isReal ? 'Standort' : 'Standort (Demo)';
                        if (hasCoords) {
                            const mapsUrl = `https://www.google.com/maps?q=${encodeURIComponent(locationInfo.lat)},${encodeURIComponent(locationInfo.lon)}`;
                            return `
                                <div class="location-badge" onclick="event.stopPropagation(); window.open('${mapsUrl}', '_blank', 'noopener');" style="cursor: pointer;">
                                    <div class="badge-label">${badgeTitle}</div>
                                    <div class="badge-location">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                            <circle cx="12" cy="10" r="3"></circle>
                                        </svg>
                                        <span>${locationInfo.label}</span>
                                    </div>
                                    ${coordText ? `<div class=\"badge-coords\">${coordText}</div>` : ''}
                                </div>
                            `;
                        }

                        return `
                            <div class="location-badge" style="cursor: default;">
                                <div class="badge-label">${badgeTitle}</div>
                                <div class="badge-location">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                    <span>${locationInfo.label}</span>
                                </div>
                            </div>
                        `;
                    })();

                    // HTML mit <a> Link
                    card.innerHTML = `
                        <a href="${linkUrl}" ${linkTarget ? `target="${linkTarget}"` : ''} style="text-decoration: none; color: inherit; display: block; width: 100%; height: 100%;">
                            <div class="stream-thumbnail" style="position: relative;">
                                ${locationBadgeMarkup}
                                <div class="live-badge">
                                    <div class="live-dot"></div>
                                    <span>LIVE</span>
                                </div>
                                ${isReserved ? '<div class="reserved-overlay"><span>RESERVIERT</span></div>' : ''}
                                <video id="preview_${currentRoomId}" class="stream-preview-video" muted playsinline style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 0;"></video>
                                <div class="placeholder">
                                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M23 7l-7 5 7 5V7z"></path>
                                        <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
                                    </svg>
                                </div>
                            </div>
                            <div class="stream-info">
                                <h3 class="stream-title">${room.roomId}</h3>
                                <div class="stream-meta">
                                    <div class="stream-location">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                            <circle cx="12" cy="10" r="3"></circle>
                                        </svg>
                                        <span>${locationInfo.label}</span>
                                    </div>
                                    <div class="stream-duration">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12 6 12 12 16 14"></polyline>
                                        </svg>
                                        <span class="duration-${room.roomId}">${duration}</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    `;

                    const thumbVideo = createThumbnailVideo(currentRoomId);
                    thumbVideo.style.pointerEvents = 'none'; // Video blockiert Klicks nicht
                    card.querySelector('.stream-thumbnail').prepend(thumbVideo);
                    
                    // Kein Klick-Block mehr bei Reserviert: Overlay ist nur visuell während Checkout
                    // Lazy Loading ist bereits in createThumbnailVideo() implementiert

                    // WebRTC Vorschau nur laden, wenn Karte sichtbar wird; beenden, wenn sie wieder verschwindet
                    const previewVideoEl = card.querySelector(`#preview_${currentRoomId}`);
                    if (previewVideoEl) {
                        const previewObserver = new IntersectionObserver(entries => {
                            entries.forEach(entry => {
                                if (entry.isIntersecting) {
                                    // Start Preview
                                    loadStreamPreview(currentRoomId);
                                } else {
                                    // Stop Preview
                                    const pc = previewConnections.get(currentRoomId);
                                    if (pc) {
                                        try { pc.close(); } catch {}
                                        previewConnections.delete(currentRoomId);
                                    }
                                    previewVideoEl.pause();
                                    previewVideoEl.srcObject = null;
                                    previewVideoEl.classList.remove('active');
                                }
                            });
                        }, { rootMargin: '0px', threshold: 0.25 });
                        previewObserver.observe(card);
                    }

                    streamsGrid.appendChild(card);
                    console.log('[displayRooms] Karte hinzugefügt für:', currentRoomId);
                    
                    // Echtzeit-Aktualisierung der Dauer (jede Sekunde)
                    const startTime = new Date(room.created).getTime();
                    const durationElement = card.querySelector(`.duration-${currentRoomId}`);
                    
                    const updateDuration = () => {
                        const elapsed = Date.now() - startTime;
                        if (durationElement) {
                            durationElement.textContent = formatDuration(elapsed);
                        }
                    };
                    
                        const interval = setInterval(updateDuration, 1000);
                        durationIntervals.push(interval);
                    } catch (error) {
                        console.error('[displayRooms] Fehler beim Erstellen der Karte für', room.roomId, ':', error);
                    }
                }
            } else {
                emptyState.style.display = 'none';
                streamsGrid.style.display = 'grid';
            }
        }

        let hasVideoStream = false;

        function showPopover(roomId, hasVideo = false, isReserved = false) {
            console.log('[showPopover] roomId:', roomId, 'hasVideo:', hasVideo, 'isReserved:', isReserved);
            
            selectedRoomId = roomId;
            hasVideoStream = hasVideo;
            
            // Wenn reserviert, zeige Hinweis (nur bei Live-Streams)
            if (isReserved && !hasVideo) {
                showReservedPopup();
                return;
            }
            
            // Video-Streams: ohne webrtc Parameter
            if (hasVideo) {
                const url = `watch-movie.php?room=${encodeURIComponent(roomId)}`;
                console.log('[showPopover] Öffne Video:', url);
                window.open(url, '_blank');
                return;
            }
            
            // Live-Streams: mit webrtc Parameter
            if (!isReserved) {
                const url = `watch-cam.php?room=${encodeURIComponent(roomId)}`;
                console.log('[showPopover] Öffne Live-Stream:', url);
                window.open(url, '_blank');
                return;
            }
            
            // Fallback: Popover anzeigen (sollte nicht mehr erreicht werden)
            document.getElementById('popoverRoomId').textContent = roomId;
            popoverOverlay.classList.add('active');
        }
        
        function showReservedPopup() {
            const popup = document.createElement('div');
            popup.style.position = 'fixed';
            popup.style.top = '0';
            popup.style.left = '0';
            popup.style.width = '100%';
            popup.style.height = '100%';
            popup.style.background = 'rgba(0, 0, 0, 0.8)';
            popup.style.display = 'flex';
            popup.style.alignItems = 'center';
            popup.style.justifyContent = 'center';
            popup.style.zIndex = '10000';
            popup.style.backdropFilter = 'blur(10px)';
            
            popup.innerHTML = `
                <div style="
                    background: linear-gradient(135deg, #1a1a24 0%, #13131a 100%);
                    padding: 40px 50px;
                    border-radius: 20px;
                    text-align: center;
                    max-width: 500px;
                    border: 1px solid rgba(212, 175, 55, 0.3);
                    box-shadow: 0 0 40px rgba(212, 175, 55, 0.2);
                ">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="2" style="margin-bottom: 20px;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <h2 style="
                        font-family: 'IBM Plex Sans', sans-serif;
                        font-size: 1.5rem;
                        font-weight: 400;
                        color: #fff;
                        margin-bottom: 15px;
                    ">Location wird aktuell genutzt</h2>
                    <p style="
                        font-family: 'DM Sans', sans-serif;
                        font-size: 1rem;
                        color: #a1a1aa;
                        margin-bottom: 30px;
                        line-height: 1.6;
                    ">Diese Location wird aktuell genutzt.<br>Probieren Sie es später nocheinmal.</p>
                    <button onclick="this.parentElement.parentElement.remove()" style="
                        background: linear-gradient(135deg, #d4af37 0%, #b8941f 100%);
                        color: #000;
                        border: none;
                        padding: 12px 30px;
                        border-radius: 8px;
                        font-family: 'DM Sans', sans-serif;
                        font-size: 1rem;
                        font-weight: 500;
                        cursor: pointer;
                        transition: transform 0.2s;
                    " onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">Verstanden</button>
                </div>
            `;
            
            document.body.appendChild(popup);
            
            // Schließen bei Klick auf Hintergrund
            popup.addEventListener('click', (e) => {
                if (e.target === popup) {
                    popup.remove();
                }
            });
        }

        function closePopover() {
            popoverOverlay.classList.remove('active');
            // selectedRoomId NICHT auf null setzen - wird noch für joinStream gebraucht
        }

        async function joinStream() {
            if (!selectedRoomId) {
                console.error('Keine Room-ID ausgewählt!');
                return;
            }

            const roomToJoin = selectedRoomId; // Speichere vor closePopover
            closePopover();
            
            // Öffne in neuem Tab
            window.open(`watch.html?room=${encodeURIComponent(roomToJoin)}`, '_blank');
            
            // Jetzt erst zurücksetzen
            selectedRoomId = null;
        }

        function playVideoStream() {
            console.log('Starte Video-Stream...');
            
            // Setze Video-Quelle
            remoteVideo.src = 'videos/ttr.m4v';
            remoteVideo.muted = false; // Mit Ton
            remoteVideo.loop = true;
            remoteVideo.controls = true;
            
            // Zeige Play-Button falls Autoplay blockiert wird
            document.getElementById('manualPlayBtn').style.display = 'block';
            
            // Warte auf Metadaten
            remoteVideo.onloadedmetadata = () => {
                console.log('Video-Metadaten geladen');
                remoteVideo.play().then(() => {
                    console.log('Video-Stream wird abgespielt');
                    document.getElementById('playerBadge').textContent = 'LIVE';
                    document.getElementById('playerBadge').classList.add('live');
                    document.getElementById('manualPlayBtn').style.display = 'none';
                }).catch(e => {
                    console.error('Video-Play-Fehler:', e);
                    // Zeige Play-Button
                    document.getElementById('manualPlayBtn').style.display = 'block';
                    document.getElementById('playerBadge').textContent = 'Klicke Play';
                });
            };
            
            remoteVideo.onerror = (e) => {
                console.error('Video-Ladefehler:', e);
                console.error('Video src:', remoteVideo.src);
                document.getElementById('playerBadge').textContent = 'Fehler beim Laden';
            };

            remoteVideo.onplay = () => {
                document.getElementById('manualPlayBtn').style.display = 'none';
                document.getElementById('playerBadge').textContent = 'LIVE';
                document.getElementById('playerBadge').classList.add('live');
            };
        }

        async function handleOffer(offer) {
            console.log('Offer empfangen:', offer);
            
            peerConnection = new RTCPeerConnection({ iceServers: ICE_SERVERS });

            peerConnection.ontrack = (event) => {
                console.log('Stream empfangen!', event.streams[0]);
                remoteVideo.srcObject = event.streams[0];
                remoteVideo.play().catch(e => console.error('Play error:', e));
                
                document.getElementById('playerBadge').textContent = 'LIVE';
                document.getElementById('playerBadge').classList.add('live');
            };

            peerConnection.onicecandidate = (event) => {
                if (event.candidate) {
                    signalingSocket.send(JSON.stringify({
                        type: 'ice-candidate',
                        roomId: currentRoomId,
                        candidate: event.candidate
                    }));
                }
            };

            peerConnection.onconnectionstatechange = () => {
                console.log('Connection state:', peerConnection.connectionState);
            };

            peerConnection.oniceconnectionstatechange = () => {
                console.log('ICE connection state:', peerConnection.iceConnectionState);
            };

            await peerConnection.setRemoteDescription(new RTCSessionDescription(offer));
            const answer = await peerConnection.createAnswer();
            await peerConnection.setLocalDescription(answer);

            console.log('Answer gesendet');
            signalingSocket.send(JSON.stringify({
                type: 'answer',
                roomId: currentRoomId,
                answer: answer
            }));
        }

        async function handleIceCandidate(candidate) {
            if (peerConnection) {
                await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
            }
        }

        // Funktion zum Laden der Stream-Vorschau
        const previewConnections = new Map();
        
        async function handlePreviewOffer(roomId, offer) {
            try {
                const pc = previewConnections.get(roomId);
                if (!pc) {
                    console.log('Keine Preview-Connection für', roomId);
                    return;
                }
                
                await pc.setRemoteDescription(new RTCSessionDescription(offer));
                const answer = await pc.createAnswer();
                await pc.setLocalDescription(answer);
                
                signalingSocket.send(JSON.stringify({
                    type: 'answer',
                    roomId: roomId,
                    answer: answer,
                    preview: true
                }));
                
                console.log('Preview Answer gesendet für', roomId);
            } catch (error) {
                console.error('Fehler bei Preview Offer:', error);
            }
        }
        
        async function loadStreamPreview(roomId) {
            try {
                console.log('Lade Vorschau für', roomId);
                
                const videoElement = document.getElementById(`preview_${roomId}`);
                if (!videoElement) {
                    console.log('Video-Element nicht gefunden für', roomId);
                    return;
                }
                
                // Erstelle WebRTC-Verbindung für Vorschau
                const pc = new RTCPeerConnection({ iceServers: ICE_SERVERS });
                previewConnections.set(roomId, pc);
                
                pc.ontrack = (event) => {
                    // Nur Videotrack für die Vorschau verwenden
                    if (event.track && event.track.kind !== 'video') return;
                    if (videoElement.srcObject) return; // bereits initialisiert

                    console.log('Vorschau-Stream empfangen für', roomId);
                    videoElement.srcObject = event.streams[0];
                    videoElement.muted = true; // Wichtig für Autoplay!
                    videoElement.classList.add('active');

                    // Versuche abzuspielen (einmalig)
                    videoElement.play()
                        .then(() => console.log('Vorschau spielt für', roomId))
                        .catch(e => console.log('Autoplay blockiert für Vorschau:', e));
                };

                pc.onconnectionstatechange = () => {
                    console.log('[Preview] Connection state:', pc.connectionState, 'für', roomId);
                    if (['failed', 'disconnected', 'closed'].includes(pc.connectionState)) {
                        try { pc.close(); } catch {}
                        previewConnections.delete(roomId);
                        if (videoElement) {
                            videoElement.pause();
                            videoElement.srcObject = null;
                            videoElement.classList.remove('active');
                        }
                    }
                };
                
                pc.onicecandidate = (event) => {
                    if (event.candidate && signalingSocket && signalingSocket.readyState === WebSocket.OPEN) {
                        signalingSocket.send(JSON.stringify({
                            type: 'ice-candidate',
                            roomId: roomId,
                            candidate: event.candidate
                        }));
                    }
                };
                
                // Sende Viewer-Request für Vorschau
                if (signalingSocket && signalingSocket.readyState === WebSocket.OPEN) {
                    signalingSocket.send(JSON.stringify({
                        type: 'viewer',
                        roomId: roomId,
                        preview: true
                    }));
                }
                
            } catch (error) {
                console.log('Fehler beim Laden der Vorschau:', error);
            }
        }

        function backToOverview() {
            if (peerConnection) {
                peerConnection.close();
                peerConnection = null;
            }

            if (statsInterval) {
                clearInterval(statsInterval);
                statsInterval = null;
            }

            // Video stoppen
            remoteVideo.pause();
            remoteVideo.src = '';
            remoteVideo.srcObject = null;
            currentRoomId = null;
            hasVideoStream = false;

            streamView.classList.remove('active');
            streamsOverview.style.display = 'block';

            // Badge zurücksetzen
            document.getElementById('playerBadge').textContent = 'Verbinde...';
            document.getElementById('playerBadge').classList.remove('live');

            loadStreams();
        }

        popoverOverlay.addEventListener('click', (e) => {
            if (e.target === popoverOverlay) closePopover();
        });

        window.addEventListener('load', init);
        window.addEventListener('beforeunload', () => {
            if (peerConnection) peerConnection.close();
            if (signalingSocket) signalingSocket.close();
        });

        // HLS.js für Thumbnail-Videos initialisieren
        document.addEventListener('DOMContentLoaded', () => {
            const thumbnailVideos = document.querySelectorAll('.thumbnail-video');
            
            thumbnailVideos.forEach(video => {
                const source = video.querySelector('source[type="application/x-mpegURL"]');
                if (!source) return;
                
                const hlsUrl = source.src;
                
                // Prüfe ob natives HLS unterstützt wird (Safari)
                if (video.canPlayType('application/vnd.apple.mpegurl')) {
                    video.src = hlsUrl;
                } else if (Hls.isSupported()) {
                    // Verwende HLS.js für andere Browser
                    const hls = new Hls({
                        enableWorker: true,
                        lowLatencyMode: false,
                        maxBufferLength: 10,
                        maxMaxBufferLength: 20
                    });
                    
                    hls.loadSource(hlsUrl);
                    hls.attachMedia(video);
                    
                    hls.on(Hls.Events.ERROR, (event, data) => {
                        if (data.fatal) {
                            console.log('HLS-Fehler für Thumbnail, verwende Fallback');
                            hls.destroy();
                            // Video fällt automatisch auf MP4-Source zurück
                        }
                    });
                }
            });
        });
    </script>

    <?php include 'components/footer.php'; ?>
</body>
</html>
