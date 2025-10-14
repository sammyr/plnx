// Video Player Controls
const video = document.getElementById('videoPlayer');
const playPauseBtn = document.getElementById('playPauseBtn');
const muteBtn = document.getElementById('muteBtn');
const volumeBar = document.getElementById('volumeBar');
const progressBar = document.getElementById('progressBar');
const currentTimeEl = document.getElementById('currentTime');
const totalDurationEl = document.getElementById('totalDuration');
const fullscreenBtn = document.getElementById('fullscreenBtn');
const videoContainer = document.querySelector('.video-container');
const customControls = document.getElementById('customControls');

// Sperre für Ton-Aktivierung (wird von watch-cam-chat.js gesetzt)
window.audioUnlocked = false;

// Lade gespeicherte Lautstärke
const savedVolume = localStorage.getItem('plnx_volume');
if (savedVolume) {
    video.volume = parseFloat(savedVolume);
    volumeBar.value = video.volume * 100;
    console.log('Lautstärke geladen:', Math.round(video.volume * 100) + '%');
}

// Play/Pause
playPauseBtn.addEventListener('click', togglePlayPause);

function togglePlayPause() {
    if (video.paused) {
        video.play();
        playPauseBtn.textContent = '⏸';
    } else {
        video.pause();
        playPauseBtn.textContent = '▶';
    }
}

// Mute/Unmute
let volumeBeforeMute = video.volume;
muteBtn.addEventListener('click', toggleMute);

function updateMuteIcon() {
    if (!window.audioUnlocked) {
        // Gesperrt: Zeige Schloss-Icon
        muteBtn.innerHTML = `
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
        `;
        muteBtn.style.color = '#ef4444'; // Rot
        muteBtn.title = 'Audio gesperrt - bitte buchen';
    } else if (video.muted) {
        // Stumm: Zeige durchgestrichenes Lautsprecher-Icon
        muteBtn.innerHTML = `
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                <line x1="23" y1="9" x2="17" y2="15"></line>
                <line x1="17" y1="9" x2="23" y2="15"></line>
            </svg>
        `;
        muteBtn.style.color = 'white';
        muteBtn.title = 'Ton einschalten';
    } else {
        // Ton an: Zeige normales Lautsprecher-Icon
        muteBtn.innerHTML = `
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                <path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path>
            </svg>
        `;
        muteBtn.style.color = 'white';
        muteBtn.title = 'Ton ausschalten';
    }
}

function toggleMute() {
    if (video.muted) {
        // Prüfe ob Audio freigeschaltet ist
        if (!window.audioUnlocked) {
            console.log('[toggleMute] Audio noch gesperrt - bitte erst buchen');
            updateMuteIcon();
            return;
        }
        video.muted = false;
        video.volume = volumeBeforeMute > 0 ? volumeBeforeMute : 0.2;
        volumeBar.value = video.volume * 100;
        localStorage.setItem('plnx_volume', video.volume);
    } else {
        volumeBeforeMute = video.volume;
        video.muted = true;
        localStorage.setItem('plnx_volume_before_mute', volumeBeforeMute);
    }
    updateMuteIcon();
}

// Initiales Icon setzen
updateMuteIcon();

// Mache Funktion global verfügbar für watch-cam-chat.js
window.updateMuteIcon = updateMuteIcon;

// Volume Control
volumeBar.addEventListener('input', (e) => {
    video.volume = e.target.value / 100;
    // Nur unmuten wenn Audio freigeschaltet ist
    if (window.audioUnlocked) {
        video.muted = false;
    }
    localStorage.setItem('plnx_volume', video.volume);
});

// Progress Bar
video.addEventListener('timeupdate', () => {
    if (video.duration) {
        const progress = (video.currentTime / video.duration) * 100;
        progressBar.value = progress;
        progressBar.style.background = `linear-gradient(to right, #d4af37 ${progress}%, rgba(255,255,255,0.2) ${progress}%)`;
        
        // Update time display
        currentTimeEl.textContent = formatTime(video.currentTime);
    }
});

progressBar.addEventListener('input', (e) => {
    const time = (e.target.value / 100) * video.duration;
    video.currentTime = time;
});

// Duration
video.addEventListener('loadedmetadata', () => {
    if (video.duration && video.duration !== Infinity) {
        totalDurationEl.textContent = formatTime(video.duration);
    } else {
        totalDurationEl.textContent = 'LIVE';
    }
});

// Fullscreen
fullscreenBtn.addEventListener('click', toggleFullscreen);

function toggleFullscreen() {
    if (!document.fullscreenElement) {
        videoContainer.requestFullscreen();
    } else {
        document.exitFullscreen();
    }
}

// Keyboard Shortcuts
document.addEventListener('keydown', (e) => {
    // Ignoriere Shortcuts wenn Input/Textarea fokussiert ist
    const activeElement = document.activeElement;
    if (activeElement && (activeElement.tagName === 'INPUT' || activeElement.tagName === 'TEXTAREA')) {
        return;
    }
    
    if (e.code === 'Space') {
        e.preventDefault();
        togglePlayPause();
    } else if (e.code === 'KeyM') {
        toggleMute();
    }
});

// Helper Functions
function formatTime(seconds) {
    if (!seconds || !isFinite(seconds)) return '00:00';
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
}

// Show/Hide Controls on hover
let controlsTimeout;
videoContainer.addEventListener('mousemove', () => {
    customControls.style.opacity = '1';
    clearTimeout(controlsTimeout);
    controlsTimeout = setTimeout(() => {
        if (!video.paused) {
            customControls.style.opacity = '0';
        }
    }, 3000);
});

videoContainer.addEventListener('mouseleave', () => {
    if (!video.paused) {
        customControls.style.opacity = '0';
    }
});

video.addEventListener('play', () => {
    playPauseBtn.textContent = '⏸';
});

video.addEventListener('pause', () => {
    playPauseBtn.textContent = '▶';
    customControls.style.opacity = '1';
});

// Auto-Unmute nach Mausbewegung (verzögert)
let autoUnmuteTriggered = false;
let mouseMoveTimeout;

function handleAutoUnmute() {
    if (!autoUnmuteTriggered && video.muted && window.audioUnlocked) {
        clearTimeout(mouseMoveTimeout);
        mouseMoveTimeout = setTimeout(() => {
            if (!autoUnmuteTriggered && video.muted) {
                autoUnmuteTriggered = true;
                video.muted = false;
                video.volume = savedVolume ? parseFloat(savedVolume) : 0.5;
                volumeBar.value = video.volume * 100;
                updateMuteIcon();
                console.log('[Auto-Unmute] Ton automatisch aktiviert nach Mausbewegung');
            }
        }, 2000); // 2 Sekunden nach letzter Mausbewegung
    }
}

// Lausche auf Mausbewegung über dem Video
videoContainer.addEventListener('mousemove', handleAutoUnmute);
document.addEventListener('mousemove', handleAutoUnmute);
