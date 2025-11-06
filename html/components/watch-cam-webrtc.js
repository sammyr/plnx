// WebRTC Logic for watch-cam.php
let peerConnection = null;
let signalingSocket = null;
let localAudioStream = null;
let isMicEnabled = false;
const roomId = new URLSearchParams(window.location.search).get('room') || 'Driver-Berlin-001';

console.log('Starte WebRTC-Stream für Raum:', roomId);

// Verbinde mit Signaling-Server
function connectSignaling() {
    const wsUrl = window.location.protocol === 'https:' ? 'wss://ws.sammyrichter.de' : 'ws://localhost:3000';
    signalingSocket = new WebSocket(wsUrl);
    
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

// Erstelle einen stummen Audio-Track als Platzhalter
function createSilentAudioTrack() {
    const ctx = new AudioContext();
    const oscillator = ctx.createOscillator();
    const dst = oscillator.connect(ctx.createMediaStreamDestination());
    oscillator.start();
    const track = dst.stream.getAudioTracks()[0];
    track.enabled = false;
    return track;
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
        
        // Füge leeren Audio-Track hinzu (wird später aktiviert wenn Chat startet)
        // Dies ermöglicht bidirektionale Audio-Kommunikation
        const silentAudioTrack = createSilentAudioTrack();
        peerConnection.addTrack(silentAudioTrack, new MediaStream());
        
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

// Aktiviere Mikrofon für bidirektionale Kommunikation
async function enableMicrophone() {
    try {
        console.log('[Mikrofon] 🎤 Aktiviere Mikrofon...');
        console.log('[Mikrofon] PeerConnection Status:', peerConnection ? 'Vorhanden' : 'Fehlt');
        
        // Zeige Lade-Indikator im Chat
        showMicrophoneStatus('Mikrofon wird aktiviert...', 'loading');
        
        // Hole Mikrofon-Zugriff
        console.log('[Mikrofon] Fordere Mikrofon-Berechtigung an...');
        localAudioStream = await navigator.mediaDevices.getUserMedia({
            audio: {
                echoCancellation: true,
                noiseSuppression: true,
                autoGainControl: true
            }
        });
        
        console.log('[Mikrofon] ✅ Mikrofon-Zugriff erhalten');
        console.log('[Mikrofon] Audio Tracks:', localAudioStream.getAudioTracks().length);
        
        // Ersetze den stummen Track mit echtem Mikrofon
        const audioTrack = localAudioStream.getAudioTracks()[0];
        console.log('[Mikrofon] Audio Track:', audioTrack.label, 'Enabled:', audioTrack.enabled);
        
        const sender = peerConnection.getSenders().find(s => s.track && s.track.kind === 'audio');
        
        if (sender) {
            await sender.replaceTrack(audioTrack);
            console.log('[Mikrofon] ✅ Audio-Track ersetzt');
        } else {
            peerConnection.addTrack(audioTrack, localAudioStream);
            console.log('[Mikrofon] ✅ Audio-Track hinzugefügt');
        }
        
        isMicEnabled = true;
        updateMicrophoneUI();
        showMicrophoneStatus('Mikrofon aktiv', 'success');
        
        console.log('[Mikrofon] 🎉 Mikrofon erfolgreich aktiviert!');
        return true;
    } catch (error) {
        console.error('[Mikrofon] ❌ Fehler beim Aktivieren:', error);
        console.error('[Mikrofon] Fehler-Details:', error.name, error.message);
        
        let errorMsg = 'Mikrofon-Zugriff fehlgeschlagen.';
        if (error.name === 'NotAllowedError') {
            errorMsg = 'Mikrofon-Berechtigung verweigert. Bitte erlaube den Zugriff in deinem Browser.';
        } else if (error.name === 'NotFoundError') {
            errorMsg = 'Kein Mikrofon gefunden. Bitte schließe ein Mikrofon an.';
        }
        
        showMicrophoneStatus(errorMsg, 'error');
        alert(errorMsg);
        return false;
    }
}

// Zeige Mikrofon-Status im Chat
function showMicrophoneStatus(message, type) {
    const chatMessages = document.getElementById('chatMessages');
    if (chatMessages) {
        const statusEl = document.createElement('div');
        statusEl.style.cssText = 'text-align: center; margin: 12px 0; font-size: 12px;';
        
        let bgColor, borderColor, icon;
        if (type === 'loading') {
            bgColor = 'rgba(212, 175, 55, 0.1)';
            borderColor = 'rgba(212, 175, 55, 0.2)';
            icon = '⏳';
        } else if (type === 'success') {
            bgColor = 'rgba(16, 185, 129, 0.1)';
            borderColor = 'rgba(16, 185, 129, 0.2)';
            icon = '✅';
        } else {
            bgColor = 'rgba(239, 68, 68, 0.1)';
            borderColor = 'rgba(239, 68, 68, 0.2)';
            icon = '❌';
        }
        
        statusEl.innerHTML = `
            <div style="background: ${bgColor}; padding: 10px; border-radius: 10px; border: 1px solid ${borderColor}; color: rgba(255, 255, 255, 0.8);">
                ${icon} ${message}
            </div>
        `;
        chatMessages.appendChild(statusEl);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
}

// Deaktiviere Mikrofon
function disableMicrophone() {
    if (localAudioStream) {
        localAudioStream.getTracks().forEach(track => track.stop());
        localAudioStream = null;
    }
    
    // Ersetze mit stummem Track
    const silentTrack = createSilentAudioTrack();
    const sender = peerConnection.getSenders().find(s => s.track && s.track.kind === 'audio');
    if (sender) {
        sender.replaceTrack(silentTrack);
    }
    
    isMicEnabled = false;
    updateMicrophoneUI();
    console.log('[Mikrofon] Mikrofon deaktiviert');
}

// Toggle Mikrofon
function toggleMicrophone() {
    if (isMicEnabled) {
        disableMicrophone();
    } else {
        enableMicrophone();
    }
}

// Aktualisiere Mikrofon-UI - Dezent
function updateMicrophoneUI() {
    const micButton = document.getElementById('micToggleBtn');
    const micIconSvg = document.getElementById('micIconSvg');
    const micStatus = document.getElementById('micStatus');
    const micMuteLine = document.getElementById('micMuteLine');
    
    if (micButton && micIconSvg && micStatus) {
        if (isMicEnabled) {
            // Mikrofon aktiv - Dezentes Grün
            micButton.style.background = 'rgba(16, 185, 129, 0.12)';
            micButton.style.borderColor = 'rgba(16, 185, 129, 0.25)';
            micStatus.textContent = 'Mikrofon aktiv';
            
            // Verstecke Mute-Linie
            if (micMuteLine) {
                micMuteLine.style.display = 'none';
            }
            
            // Ändere Icon-Farbe zu Grün
            const paths = micIconSvg.querySelectorAll('path, line');
            paths.forEach(path => {
                if (path.id !== 'micMuteLine') {
                    path.style.stroke = 'rgba(16, 185, 129, 0.9)';
                }
            });
            
            // Icon-Opacity
            micIconSvg.style.opacity = '1';
        } else {
            // Mikrofon aus - Dezentes Rot
            micButton.style.background = 'rgba(239, 68, 68, 0.08)';
            micButton.style.borderColor = 'rgba(239, 68, 68, 0.2)';
            micStatus.textContent = 'Mikrofon aus';
            
            // Zeige Mute-Linie
            if (micMuteLine) {
                micMuteLine.style.display = 'block';
            }
            
            // Ändere Icon-Farbe zu Weiß
            const paths = micIconSvg.querySelectorAll('path, line');
            paths.forEach(path => {
                path.style.stroke = 'currentColor';
            });
            
            // Icon-Opacity
            micIconSvg.style.opacity = '0.8';
        }
    }
}

// Exportiere Funktionen für Chat-Integration
window.enableViewerMicrophone = enableMicrophone;
window.disableViewerMicrophone = disableMicrophone;
window.toggleViewerMicrophone = toggleMicrophone;

// Starte WebRTC
initWebRTC();
connectSignaling();
