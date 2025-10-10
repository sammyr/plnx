// WebRTC Logic for watch-cam.php
let peerConnection = null;
let signalingSocket = null;
const roomId = new URLSearchParams(window.location.search).get('room') || 'Driver-Berlin-001';

console.log('Starte WebRTC-Stream für Raum:', roomId);

// Verbinde mit Signaling-Server
function connectSignaling() {
    signalingSocket = new WebSocket('ws://localhost:3000');
    
    signalingSocket.onopen = () => {
        console.log('Mit Signaling-Server verbunden');
        signalingSocket.send(JSON.stringify({
            type: 'viewer',
            roomId: roomId
        }));
    };
    
    signalingSocket.onmessage = async (event) => {
        const data = JSON.parse(event.data);
        
        switch (data.type) {
            case 'viewer-ready':
                console.log('Viewer bereit');
                break;
                
            case 'offer':
                console.log('Offer empfangen:', data.offer);
                await handleOffer(data.offer);
                break;
                
            case 'ice-candidate':
                await handleIceCandidate(data.candidate);
                break;
                
            case 'broadcaster-left':
                console.log('Broadcaster hat Stream beendet');
                showError('Stream wurde beendet');
                break;
                
            case 'error':
                console.error('Signaling-Fehler:', data.message);
                showError(data.message);
                break;
        }
    };
    
    signalingSocket.onerror = (error) => {
        console.error('WebSocket-Fehler:', error);
    };
    
    signalingSocket.onclose = () => {
        console.log('Verbindung zum Signaling-Server geschlossen');
        setTimeout(connectSignaling, 3000);
    };
}

// Initialisiere WebRTC
async function initWebRTC() {
    try {
        peerConnection = new RTCPeerConnection({
            iceServers: [
                { urls: 'stun:stun.l.google.com:19302' },
                { urls: 'stun:stun1.l.google.com:19302' }
            ]
        });
        
        peerConnection.ontrack = (event) => {
            console.log('WebRTC-Stream empfangen!', event.streams[0]);
            const video = document.getElementById('videoPlayer');
            if (video && event.streams[0]) {
                video.srcObject = event.streams[0];
                
                // Versuche automatisches Abspielen
                const playPromise = video.play();
                if (playPromise !== undefined) {
                    playPromise
                        .then(() => {
                            console.log('Video spielt automatisch ab');
                        })
                        .catch(error => {
                            console.warn('Autoplay blockiert:', error.message);
                            // Zeige Play-Button oder Info
                            showPlayButton();
                        });
                }
            }
        };
        
        peerConnection.onicecandidate = (event) => {
            if (event.candidate && signalingSocket.readyState === WebSocket.OPEN) {
                signalingSocket.send(JSON.stringify({
                    type: 'ice-candidate',
                    candidate: event.candidate,
                    roomId: roomId
                }));
            }
        };
        
        peerConnection.onconnectionstatechange = () => {
            console.log('Peer Connection State:', peerConnection.connectionState);
            if (peerConnection.connectionState === 'connected') {
                const loadingIndicator = document.getElementById('loadingIndicator');
                if (loadingIndicator) {
                    loadingIndicator.style.display = 'none';
                    console.log('Loading für WebRTC versteckt');
                }
            }
        };
        
    } catch (error) {
        console.error('WebRTC Initialisierung fehlgeschlagen:', error);
        showError('WebRTC-Initialisierung fehlgeschlagen');
    }
}

async function handleOffer(offer) {
    if (!peerConnection) {
        await initWebRTC();
    }
    
    try {
        await peerConnection.setRemoteDescription(new RTCSessionDescription(offer));
        const answer = await peerConnection.createAnswer();
        await peerConnection.setLocalDescription(answer);
        
        if (signalingSocket.readyState === WebSocket.OPEN) {
            signalingSocket.send(JSON.stringify({
                type: 'answer',
                answer: answer,
                roomId: roomId
            }));
            console.log('Answer gesendet');
        }
    } catch (error) {
        console.error('Fehler beim Verarbeiten des Offers:', error);
    }
}

async function handleIceCandidate(candidate) {
    if (peerConnection) {
        try {
            await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
        } catch (error) {
            console.error('Fehler beim Hinzufügen des ICE-Kandidaten:', error);
        }
    }
}

function showError(message) {
    const loadingIndicator = document.getElementById('loadingIndicator');
    if (loadingIndicator) {
        loadingIndicator.innerHTML = `
            <div style="text-align: center; color: #ef4444;">
                <div style="font-size: 48px; margin-bottom: 16px;">❌</div>
                <div style="font-size: 18px;">${message}</div>
            </div>
        `;
        loadingIndicator.style.display = 'block';
    }
}

function showPlayButton() {
    const loadingIndicator = document.getElementById('loadingIndicator');
    if (loadingIndicator) {
        loadingIndicator.innerHTML = `
            <div style="text-align: center;">
                <button onclick="document.getElementById('videoPlayer').play(); this.parentElement.parentElement.style.display='none';" 
                        style="background: linear-gradient(135deg, #d4af37, #f4d03f); border: none; color: #000; padding: 20px 40px; border-radius: 50%; font-size: 32px; cursor: pointer; box-shadow: 0 10px 30px rgba(212, 175, 55, 0.5); transition: all 0.3s;"
                        onmouseover="this.style.transform='scale(1.1)'"
                        onmouseout="this.style.transform='scale(1)'">
                    ▶
                </button>
                <div style="color: var(--text-secondary); font-size: 14px; margin-top: 16px;">Klicke zum Abspielen</div>
            </div>
        `;
        loadingIndicator.style.display = 'block';
    }
}

// Starte WebRTC
initWebRTC();
connectSignaling();
