<?php
// Broadcaster-Seite (PHP-Version)
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Broadcaster</title>
    <?php include 'components/head-meta.php'; ?>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@200;300;400;500;600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="components/global.css">
</head>
<body>
    <?php include 'components/header.php'; ?>
    <div class="broadcaster-container">
        <div class="header" style="margin-top:20px;">
 
        </div>

        <div class="broadcaster-card">
            <div class="broadcaster-video-container">
                <video id="localVideo" autoplay muted playsinline></video>
                <div class="broadcaster-video-overlay">
                    <div id="statusBadge" class="status-badge"><div class="status-dot"></div><span>Bereit</span></div>
                    <!-- Location Badge (oben rechts) -->
                    <div class="stats-badge" id="locationBadge" style="display:flex; align-items:center; gap:12px;">
                        <div id="locationRoom" style="color:#d4af37; font-size:13px; font-weight:600; letter-spacing:.5px;">-</div>
                        <a id="locationLink" href="#" target="_blank" style="color:rgba(255,255,255,.8); font-size:12px; font-weight:500; text-decoration:none; border-left:1px solid rgba(212,175,55,.3); padding-left:12px;">
                            📍 <span id="locationText">-</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="broadcaster-controls">
                <div class="broadcaster-connection-indicator">
                    <div class="broadcaster-connection-dot" id="connectionDot"></div>
                    <span id="connectionStatus">Nicht verbunden</span>
                </div>

                <div class="broadcaster-controls-grid">
                    <div class="broadcaster-form-group">
                        <label>Kamera</label>
                        <select id="cameraSelect"><option>Lade Kameras...</option></select>
                    </div>
                    <div class="broadcaster-form-group">
                        <label>Qualität</label>
                        <select id="qualitySelect">
                            <option value="1280x720" selected>HD (1280x720)</option>
                            <option value="1920x1080">Full HD (1920x1080)</option>
                            <option value="854x480">SD (854x480)</option>
                            <option value="640x360">Low (640x360)</option>
                        </select>
                    </div>
                    <div class="broadcaster-form-group">
                        <label>Room-ID (optional)</label>
                        <input type="text" id="roomIdInput" placeholder="Automatisch generiert">
                    </div>
                    <div class="broadcaster-form-group">
                        <label>Telefonnummer (Erreichbarkeit)</label>
                        <input type="tel" id="phoneInput" placeholder="z.B. +49 160 1234567">
                    </div>
                </div>

                <div class="broadcaster-button-group">
                    <button id="startCameraBtn" class="broadcaster-btn broadcaster-btn-primary"><span>Kamera starten</span></button>
                    <button id="startBroadcastBtn" class="broadcaster-btn broadcaster-btn-success" disabled><span>Broadcast starten</span></button>
                    <button id="stopBtn" class="broadcaster-btn broadcaster-btn-danger" disabled><span>Stoppen</span></button>
                </div>

                <div id="roomInfo" style="display:none;">
                    <div class="broadcaster-room-info">
                        <p>Stream-ID</p>
                        <div class="broadcaster-room-id" id="roomIdDisplay">-</div>
                        <!-- Zuschauer entfernt -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>

    <script>
        const HOSTNAME = window.location.hostname;
        const SIGNALING_SERVER = window.location.protocol === 'https:' ? `wss://ws.sammyrichter.de` : `ws://${HOSTNAME}:3000`;
        const ICE_SERVERS = [ { urls: 'stun:stun.l.google.com:19302' }, { urls: 'stun:stun1.l.google.com:19302' } ];
        let localStream=null, signalingSocket=null, peerConnections=new Map(), roomId=null, isBroadcasting=false, startTime=null, durationInterval=null, statsInterval=null;
        const localVideo=document.getElementById('localVideo');
        const cameraSelect=document.getElementById('cameraSelect');
        const qualitySelect=document.getElementById('qualitySelect');
        const roomIdInput=document.getElementById('roomIdInput');
        const phoneInput=document.getElementById('phoneInput');
        const startCameraBtn=document.getElementById('startCameraBtn');
        const startBroadcastBtn=document.getElementById('startBroadcastBtn');
        const stopBtn=document.getElementById('stopBtn');
        const statusBadge=document.getElementById('statusBadge');
        const roomInfo=document.getElementById('roomInfo');
        const roomIdDisplay=document.getElementById('roomIdDisplay');
        const viewerCountText=document.getElementById('viewerCountText');
        const connectionDot=document.getElementById('connectionDot');
        const connectionStatus=document.getElementById('connectionStatus');
        const statsBadge=document.getElementById('statsBadge');

        async function getCurrentCity(){try{const r=await fetch('https://ipapi.co/json/',{timeout:3000});const d=await r.json();return d.city||'Berlin'}catch(e){return 'Berlin'}}
        async function getNextStreamNumber(city){try{const protocol=window.location.protocol==='https:'?'https:':'http:';const r=await fetch(`${protocol}//ws.sammyrichter.de/drivers`);const d=await r.json();const nums=d.drivers.map(x=>x.driverId).filter(id=>id.startsWith(`Driver-${city}-`)).map(id=>{const m=id.match(/Driver-.*-(\d+)$/);return m?parseInt(m[1]):0});const max=nums.length>0?Math.max(...nums):0;return max+1}catch(e){return 1}}

        async function loadCameras(){try{const devs=await navigator.mediaDevices.enumerateDevices();const vids=devs.filter(d=>d.kind==='videoinput');cameraSelect.innerHTML='';vids.forEach((device,i)=>{const o=document.createElement('option');o.value=device.deviceId;o.text=device.label||`Kamera ${i+1}`;cameraSelect.appendChild(o)});}catch(e){console.error('Kameras:',e)}}

        async function startCamera(){try{const deviceId=cameraSelect.value;const [w,h]=qualitySelect.value.split('x').map(Number);const constraints={video:{deviceId:deviceId?{exact:deviceId}:undefined,width:{ideal:w},height:{ideal:h},frameRate:{ideal:30,max:60}},audio:{echoCancellation:true,noiseSuppression:true,autoGainControl:true}};localStream=await navigator.mediaDevices.getUserMedia(constraints);localVideo.srcObject=localStream;startCameraBtn.disabled=true;startBroadcastBtn.disabled=false;cameraSelect.disabled=true;qualitySelect.disabled=true;statusBadge.querySelector('span').textContent='Kamera aktiv';await loadCameras();}catch(e){alert('Fehler beim Kamera-Zugriff');}}

        async function startBroadcast(){try{if(!localStream){alert('Bitte starte zuerst die Kamera!');return}if(!roomIdInput.value.trim()){const city=await getCurrentCity();const n=await getNextStreamNumber(city);roomId=`Driver-${city}-${String(n).padStart(3,'0')}`;}else{roomId=roomIdInput.value.trim();}
            await connectToSignalingServer();
            let gpsCoords=null;try{const pos=await new Promise((res,rej)=>{navigator.geolocation.getCurrentPosition(res,rej,{timeout:5000,enableHighAccuracy:true});});gpsCoords={lat:pos.coords.latitude,lon:pos.coords.longitude};}catch(e){}
            // Telefonnummer optional mitsenden (nur Anzeige-/Logzwecke; Server kann erweitert werden)
            const phone = phoneInput && phoneInput.value ? phoneInput.value : null;
            signalingSocket.send(JSON.stringify({type:'broadcaster',roomId,location:gpsCoords,phone}));
            isBroadcasting=true;startBroadcastBtn.disabled=true;stopBtn.disabled=false;roomIdInput.disabled=true;statusBadge.classList.add('live');statusBadge.querySelector('span').textContent='LIVE';roomIdDisplay.textContent=roomId;roomInfo.style.display='block';
            // Location Badge füllen
            try{
                document.getElementById('locationRoom').textContent=roomId.toUpperCase();
                const res=await fetch(`data/locations.json?t=${Date.now()}`,{cache:'no-store'});
                if(res.ok){
                    const locations=await res.json();
                    const loc=locations[roomId];
                    if(loc){
                        // Vollformat wie Watch-Cam: Stadt, Bezirk, Straße
                        let street='';
                        if(typeof loc.address==='string'){
                            const parts=loc.address.split(', ');
                            street=parts.length>1?parts[1]:loc.address;
                        }
                        const label=[loc.city, loc.district, street].filter(Boolean).join(', ');
                        document.getElementById('locationText').textContent=label;
                        const link=`https://www.google.com/maps?q=${loc.lat},${loc.lon}`;
                        document.getElementById('locationLink').href=link;
                    } else {
                        // Fallback: aus RoomID
                        const parts=roomId.split('-');
                        if(parts.length>=3){
                            document.getElementById('locationText').textContent=`${parts[1]}, ${parts[2]}`;
                        }
                    }
                }
            }catch(err){console.warn('Location Badge:',err)}

            startTime=Date.now();durationInterval=setInterval(updateDuration,1000);statsInterval=setInterval(updateStats,2000); if(!localVideo.srcObject){localVideo.srcObject=localStream;} localVideo.play().catch(()=>{});
        }catch(e){console.error('Broadcast:',e)}}

        function connectToSignalingServer(){return new Promise((resolve,reject)=>{signalingSocket=new WebSocket(SIGNALING_SERVER);signalingSocket.onopen=()=>{connectionDot.classList.add('connected');connectionStatus.textContent='Verbunden mit Server';resolve();};signalingSocket.onmessage=handleSignalingMessage;signalingSocket.onerror=reject;signalingSocket.onclose=()=>{connectionDot.classList.remove('connected');connectionStatus.textContent='Getrennt';};});}
        async function handleSignalingMessage(event){const data=JSON.parse(event.data);switch(data.type){case 'viewer-joined':await handleViewerJoined(data.peerId);updateViewerCount();break;case 'viewer-left':handleViewerLeft(data.peerId);updateViewerCount();break;case 'answer':await handleAnswer(data.peerId,data.answer);break;case 'ice-candidate':await handleIceCandidate(data.peerId,data.candidate);break;}}
        async function handleViewerJoined(peerId){const pc=new RTCPeerConnection({iceServers:ICE_SERVERS});peerConnections.set(peerId,pc);localStream.getTracks().forEach(t=>pc.addTrack(t,localStream));pc.onicecandidate=(e)=>{if(e.candidate){signalingSocket.send(JSON.stringify({type:'ice-candidate',roomId,candidate:e.candidate,peerId}));}};const offer=await pc.createOffer();await pc.setLocalDescription(offer);signalingSocket.send(JSON.stringify({type:'offer',roomId,offer,peerId}));}
        async function handleAnswer(peerId,answer){const pc=peerConnections.get(peerId);if(pc){await pc.setRemoteDescription(new RTCSessionDescription(answer));}}
        async function handleIceCandidate(peerId,candidate){const pc=peerConnections.get(peerId);if(pc){await pc.addIceCandidate(new RTCIceCandidate(candidate));}}
        function handleViewerLeft(peerId){const pc=peerConnections.get(peerId);if(pc){pc.close();peerConnections.delete(peerId);}}
        function updateViewerCount(){const c=peerConnections.size; if(viewerCountText){viewerCountText.textContent=c;} const vc=document.getElementById('viewerCount'); if(vc){vc.textContent=c;}}
        function updateDuration(){if(!startTime)return;const el=Math.floor((Date.now()-startTime)/1000);const m=Math.floor(el/60);const s=el%60;const durEl=document.getElementById('duration'); if(durEl){durEl.textContent=`${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;}}
        async function updateStats(){if(peerConnections.size===0)return;let total=0;for(const [id,pc] of peerConnections){const stats=await pc.getStats();stats.forEach(r=>{if(r.type==='outbound-rtp'&&r.mediaType==='video'&&r.bytesSent){total+=(r.bytesSent*8)/1000;}});}const br=document.getElementById('bitrate'); if(br){br.textContent=Math.round(total);} }        
        startCameraBtn.addEventListener('click', startCamera);
        startBroadcastBtn.addEventListener('click', startBroadcast);
        stopBtn.addEventListener('click', ()=>{if(localStream){localStream.getTracks().forEach(t=>t.stop());localStream=null;}peerConnections.forEach(pc=>pc.close());peerConnections.clear();if(signalingSocket){signalingSocket.close();signalingSocket=null;}localVideo.srcObject=null;isBroadcasting=false;startCameraBtn.disabled=false;startBroadcastBtn.disabled=true;stopBtn.disabled=true;cameraSelect.disabled=false;qualitySelect.disabled=false;roomIdInput.disabled=false;statusBadge.classList.remove('live');statusBadge.querySelector('span').textContent='Gestoppt';statsBadge.style.display='none';roomInfo.style.display='none';connectionDot.classList.remove('connected');connectionStatus.textContent='Nicht verbunden';});
        window.addEventListener('load', loadCameras);
    </script>
</body>
</html>
