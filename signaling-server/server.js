const express = require('express');
const WebSocket = require('ws');
const http = require('http');
const cors = require('cors');
const fs = require('fs');
const path = require('path');
const multer = require('multer');
const { spawn } = require('child_process');

const app = express();
app.use(cors());
app.use(express.json());

// Multer für Thumbnail-Uploads
const storage = multer.diskStorage({
    destination: (req, file, cb) => {
        // Im Docker-Container ist html/ unter /app/html gemountet
        const thumbDir = path.join(__dirname, 'html', 'thumbnails');
        if (!fs.existsSync(thumbDir)) {
            fs.mkdirSync(thumbDir, { recursive: true });
            console.log('📁 Thumbnail-Ordner erstellt:', thumbDir);
        }
        cb(null, thumbDir);
    },
    filename: (req, file, cb) => {
        const roomId = req.body.roomId || 'unknown';
        cb(null, `${roomId}.jpg`);
    }
});
const upload = multer({ storage, limits: { fileSize: 5 * 1024 * 1024 } });

const server = http.createServer(app);
const wss = new WebSocket.Server({ server });

// Speichere aktive Broadcaster und Viewer
const broadcasters = new Map();
const viewers = new Map();
const rooms = new Map();
const reservedRooms = new Set(); // Reservierte Streams
const hlsProcs = new Map();

console.log('🚀 WebRTC Signaling Server gestartet');

// WebSocket-Verbindungen
wss.on('connection', (ws) => {
    console.log('📱 Neue Verbindung');

    ws.on('message', (message) => {
        try {
            const data = JSON.parse(message);
            handleMessage(ws, data);
        } catch (error) {
            console.error('❌ Fehler beim Parsen der Nachricht:', error);
        }
    });

    ws.on('close', () => {
        handleDisconnect(ws);
    });

    ws.on('error', (error) => {
        console.error('❌ WebSocket-Fehler:', error);
    });
});

function handleMessage(ws, data) {
    const { type, streamId, roomId, offer, answer, candidate, peerId, location, preview } = data;

    switch (type) {
        case 'broadcaster':
            handleBroadcaster(ws, streamId || roomId, location);
            break;

        case 'viewer':
            handleViewer(ws, streamId || roomId, !!preview);
            break;

        case 'offer':
            handleOffer(ws, roomId, offer, peerId);
            break;

        case 'answer':
            handleAnswer(ws, roomId, answer, peerId);
            break;

        case 'ice-candidate':
            handleIceCandidate(ws, roomId, candidate, peerId);
            break;

        case 'get-rooms':
            handleGetRooms(ws);
            break;

        default:
            console.log('⚠️ Unbekannter Nachrichtentyp:', type);
    }
}

function handleBroadcaster(ws, roomId, location = null) {
    ws.roomId = roomId;
    ws.role = 'broadcaster';
    
    broadcasters.set(roomId, ws);
    
    if (!rooms.has(roomId)) {
        rooms.set(roomId, {
            broadcaster: ws,
            viewers: new Set(),
            created: Date.now(),
            location: location // GPS-Koordinaten speichern
        });
    }

    console.log(`📡 Broadcaster registriert: ${roomId}${location ? ` (GPS: ${location.lat}, ${location.lon})` : ''}`);
    
    ws.send(JSON.stringify({
        type: 'broadcaster-ready',
        roomId: roomId,
        message: 'Broadcaster erfolgreich registriert'
    }));

    // Benachrichtige alle Clients über neuen Stream
    broadcastToAll({
        type: 'room-available',
        roomId: roomId
    });
}

function handleViewer(ws, roomId, preview = false) {
    ws.roomId = roomId;
    ws.role = 'viewer';
    ws.peerId = 'viewer_' + Math.random().toString(36).substr(2, 9);
    ws.preview = !!preview;

    const room = rooms.get(roomId);
    
    if (!room || !room.broadcaster) {
        ws.send(JSON.stringify({
            type: 'error',
            message: 'Stream nicht gefunden'
        }));
        return;
    }

    room.viewers.add(ws);
    viewers.set(ws.peerId, ws);

    console.log(`👁️ Viewer verbunden: ${roomId} (${room.viewers.size} Viewer)`);

    // Sende Viewer-Info an Broadcaster
    if (room.broadcaster.readyState === WebSocket.OPEN) {
        room.broadcaster.send(JSON.stringify({
            type: 'viewer-joined',
            peerId: ws.peerId,
            viewerCount: room.viewers.size,
            roomId: roomId,
            preview: ws.preview
        }));
    }

    ws.send(JSON.stringify({
        type: 'viewer-ready',
        roomId: roomId,
        peerId: ws.peerId,
        preview: ws.preview
    }));
}

function handleOffer(ws, roomId, offer, targetPeerId) {
    const room = rooms.get(roomId);
    if (!room) return;

    if (ws.role === 'broadcaster') {
        // Broadcaster sendet Offer an Viewer
        const viewer = Array.from(room.viewers).find(v => v.peerId === targetPeerId);
        if (viewer && viewer.readyState === WebSocket.OPEN) {
            viewer.send(JSON.stringify({
                type: 'offer',
                offer: offer,
                peerId: 'broadcaster',
                roomId: roomId,
                preview: viewer.preview || false
            }));
            console.log(`📤 Offer gesendet: Broadcaster → Viewer ${targetPeerId}`);
        }
    } else {
        // Viewer sendet Offer an Broadcaster
        if (room.broadcaster && room.broadcaster.readyState === WebSocket.OPEN) {
            room.broadcaster.send(JSON.stringify({
                type: 'offer',
                offer: offer,
                peerId: ws.peerId
            }));
            console.log(`📤 Offer gesendet: Viewer ${ws.peerId} → Broadcaster`);
        }
    }
}

function handleAnswer(ws, roomId, answer, targetPeerId) {
    const room = rooms.get(roomId);
    if (!room) return;

    if (ws.role === 'viewer') {
        // Viewer sendet Answer an Broadcaster
        if (room.broadcaster && room.broadcaster.readyState === WebSocket.OPEN) {
            room.broadcaster.send(JSON.stringify({
                type: 'answer',
                answer: answer,
                peerId: ws.peerId,
                roomId: roomId,
                preview: ws.preview || false
            }));
            console.log(`📥 Answer gesendet: Viewer ${ws.peerId} → Broadcaster`);
        }
    } else {
        // Broadcaster sendet Answer an Viewer
        const viewer = Array.from(room.viewers).find(v => v.peerId === targetPeerId);
        if (viewer && viewer.readyState === WebSocket.OPEN) {
            viewer.send(JSON.stringify({
                type: 'answer',
                answer: answer,
                peerId: 'broadcaster'
            }));
            console.log(`📥 Answer gesendet: Broadcaster → Viewer ${targetPeerId}`);
        }
    }
}

function handleIceCandidate(ws, roomId, candidate, targetPeerId) {
    const room = rooms.get(roomId);
    if (!room) return;

    if (ws.role === 'broadcaster') {
        // Broadcaster sendet ICE an Viewer
        const viewer = Array.from(room.viewers).find(v => v.peerId === targetPeerId);
        if (viewer && viewer.readyState === WebSocket.OPEN) {
            viewer.send(JSON.stringify({
                type: 'ice-candidate',
                candidate: candidate,
                peerId: 'broadcaster'
            }));
        }
    } else {
        // Viewer sendet ICE an Broadcaster
        if (room.broadcaster && room.broadcaster.readyState === WebSocket.OPEN) {
            room.broadcaster.send(JSON.stringify({
                type: 'ice-candidate',
                candidate: candidate,
                peerId: ws.peerId,
                roomId: roomId,
                preview: ws.preview || false
            }));
        }
    }
}

function handleGetRooms(ws) {
    const activeRooms = Array.from(rooms.entries()).map(([roomId, room]) => ({
        roomId: roomId,
        viewerCount: room.viewers.size,
        created: room.created,
        location: room.location || null,
        reserved: reservedRooms.has(roomId) // Reservierungs-Status
    }));

    ws.send(JSON.stringify({
        type: 'rooms-list',
        rooms: activeRooms
    }));
}

function handleDisconnect(ws) {
    const { roomId, role, peerId } = ws;

    if (!roomId) return;

    const room = rooms.get(roomId);
    if (!room) return;

    if (role === 'broadcaster') {
        console.log(`📡 Broadcaster getrennt: ${roomId}`);
        
        // Benachrichtige alle Viewer
        room.viewers.forEach(viewer => {
            if (viewer.readyState === WebSocket.OPEN) {
                viewer.send(JSON.stringify({
                    type: 'broadcaster-left',
                    message: 'Stream beendet'
                }));
            }
        });

        broadcasters.delete(roomId);
        rooms.delete(roomId);
        
        // Reservierung automatisch aufheben wenn Stream offline geht
        if (reservedRooms.has(roomId)) {
            reservedRooms.delete(roomId);
            console.log(`🔓 Reservierung automatisch aufgehoben (Stream offline): ${roomId}`);
        }

        // Benachrichtige alle über beendeten Stream
        broadcastToAll({
            type: 'room-closed',
            roomId: roomId
        });

    } else if (role === 'viewer') {
        console.log(`👁️ Viewer getrennt: ${roomId}`);
        
        room.viewers.delete(ws);
        viewers.delete(peerId);

        // Benachrichtige Broadcaster
        if (room.broadcaster && room.broadcaster.readyState === WebSocket.OPEN) {
            room.broadcaster.send(JSON.stringify({
                type: 'viewer-left',
                peerId: peerId,
                viewerCount: room.viewers.size
            }));
        }
    }
}

function broadcastToAll(message) {
    wss.clients.forEach(client => {
        if (client.readyState === WebSocket.OPEN) {
            client.send(JSON.stringify(message));
        }
    });
}

// HTTP-Endpunkte
app.get('/health', (req, res) => {
    res.json({
        status: 'ok',
        rooms: rooms.size,
        broadcasters: broadcasters.size,
        viewers: viewers.size,
        connections: wss.clients.size
    });
});

app.get('/rooms', (req, res) => {
    const activeRooms = Array.from(rooms.entries()).map(([roomId, room]) => ({
        roomId: roomId,
        viewerCount: room.viewers.size,
        created: new Date(room.created).toISOString(),
        location: room.location || null, // GPS-Koordinaten mit ausgeben
        reserved: reservedRooms.has(roomId) // Reservierungs-Status
    }));

    res.json({
        rooms: activeRooms,
        total: activeRooms.length
    });
});

// Alias: /drivers (same as /rooms, for compatibility)
app.get('/drivers', (req, res) => {
    const activeDrivers = Array.from(rooms.entries()).map(([roomId, room]) => ({
        driverId: roomId,
        viewerCount: room.viewers.size,
        created: new Date(room.created).toISOString(),
        location: room.location || null,
        reserved: reservedRooms.has(roomId)
    }));

    res.json({
        drivers: activeDrivers,
        total: activeDrivers.length
    });
});

// HLS Start/Stop Endpunkte
app.post('/api/hls/start', (req, res) => {
    const roomId = (req.query.room || 'demo_video_stream').toString();
    const file = (req.query.file || 'Subway.m4v').toString();

    if (hlsProcs.has(roomId)) return res.json({ success: true, roomId, running: true });

    const src = path.join('/videos', file);
    const dst = `rtmp://srs:1935/live/${roomId}`;
    const args = ['-re', '-stream_loop', '-1', '-i', src, '-c', 'copy', '-f', 'flv', dst];
    const proc = spawn('ffmpeg', args, { stdio: 'ignore' });
    hlsProcs.set(roomId, proc);
    proc.on('exit', () => hlsProcs.delete(roomId));

    res.json({ success: true, roomId, running: true });
});

app.post('/api/hls/stop', (req, res) => {
    const roomId = (req.query.room || 'demo_video_stream').toString();
    const p = hlsProcs.get(roomId);
    if (p) {
        try { p.kill('SIGINT'); } catch (e) {}
        hlsProcs.delete(roomId);
    }
    res.json({ success: true, roomId, running: false });
});

// Reservierungs-Endpunkte
app.post('/api/reserve/:roomId', (req, res) => {
    const { roomId } = req.params;
    
    // Wenn _method=DELETE (von sendBeacon), behandle als DELETE
    if (req.body && req.body._method === 'DELETE') {
        reservedRooms.delete(roomId);
        console.log(`🔓 Reservierung aufgehoben (Beacon): ${roomId}`);
        
        broadcastToAll({
            type: 'room-unreserved',
            roomId: roomId
        });
        
        res.json({ success: true, roomId, reserved: false });
    } else {
        // Normale Reservierung
        reservedRooms.add(roomId);
        console.log(`🔒 Stream reserviert: ${roomId}`);
        
        broadcastToAll({
            type: 'room-reserved',
            roomId: roomId
        });
        
        res.json({ success: true, roomId, reserved: true });
    }
});

app.delete('/api/reserve/:roomId', (req, res) => {
    const { roomId } = req.params;
    reservedRooms.delete(roomId);
    console.log(`🔓 Reservierung aufgehoben: ${roomId}`);
    
    // Benachrichtige alle Clients über Änderung
    broadcastToAll({
        type: 'room-unreserved',
        roomId: roomId
    });
    
    res.json({ success: true, roomId, reserved: false });
});

// Thumbnail-Upload-Endpunkt (speichert mehrere Versionen für Slideshow)
app.post('/api/upload-thumbnail', upload.single('thumbnail'), (req, res) => {
    const roomId = req.body.roomId || 'unknown';
    const maxSlides = 30; // Anzahl der gespeicherten Bilder (3 Sekunden bei 10 FPS)
    
    if (req.file && roomId !== 'unknown') {
        const thumbDir = path.dirname(req.file.path);
        const timestamp = Date.now();
        
        try {
            // Rotiere alte Thumbnails: 4→5, 3→4, 2→3, 1→2
            for (let i = maxSlides - 1; i > 0; i--) {
                const oldFile = path.join(thumbDir, `${roomId}_${i}.jpg`);
                const newFile = path.join(thumbDir, `${roomId}_${i + 1}.jpg`);
                if (fs.existsSync(oldFile)) {
                    if (fs.existsSync(newFile)) fs.unlinkSync(newFile);
                    fs.renameSync(oldFile, newFile);
                }
            }
            
            // Aktuelles Bild als _1.jpg speichern
            const newPath = path.join(thumbDir, `${roomId}_1.jpg`);
            if (fs.existsSync(newPath)) fs.unlinkSync(newPath);
            fs.renameSync(req.file.path, newPath);
            
            // Hauptbild (ohne Nummer) als Kopie von _1
            const mainPath = path.join(thumbDir, `${roomId}.jpg`);
            fs.copyFileSync(newPath, mainPath);
            
            console.log(`📸 Thumbnail gespeichert: ${roomId} (${maxSlides} Slides)`);
        } catch (e) {
            console.warn('Thumbnail-Rotation fehlgeschlagen:', e.message);
        }
    }
    
    res.json({ success: true, roomId });
});

// Server starten
const PORT = process.env.PORT || 3000;
server.listen(PORT, () => {
    console.log(`
╔════════════════════════════════════════════════════════════╗
║                                                            ║
║     🎥 WebRTC Signaling Server läuft!                     ║
║                                                            ║
║     Port:        ${PORT}                                        ║
║     WebSocket:   ws://localhost:${PORT}                        ║
║     Health:      http://localhost:${PORT}/health               ║
║     Rooms:       http://localhost:${PORT}/rooms                ║
║                                                            ║
╚════════════════════════════════════════════════════════════╝
    `);
});

// Cleanup alter Räume (nach 1 Stunde Inaktivität)
setInterval(() => {
    const now = Date.now();
    const maxAge = 60 * 60 * 1000; // 1 Stunde

    rooms.forEach((room, roomId) => {
        if (now - room.created > maxAge && room.viewers.size === 0) {
            console.log(`🧹 Räume aufgeräumt: ${roomId}`);
            rooms.delete(roomId);
            broadcasters.delete(roomId);
        }
    });
}, 5 * 60 * 1000); // Alle 5 Minuten prüfen
