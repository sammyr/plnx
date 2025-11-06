// Chat Functions for watch-cam.php
let chatTimeInterval = null;
let chatStartTime = null;

function openChatWindow() {
    console.log('[openChatWindow] Funktion gestartet');
    
    const chatWindow = document.getElementById('chatWindow');
    const chatContainer = document.getElementById('chatWindowContainer');
    const sidebar = document.getElementById('sidebar');
    const paymentPopover = document.getElementById('paymentPopover');
    
    console.log('[openChatWindow] chatWindow:', chatWindow);
    console.log('[openChatWindow] chatContainer:', chatContainer);
    console.log('[openChatWindow] sidebar:', sidebar);
    
    // Schließe Payment-Popover sofort beim Öffnen des Chats
    if (paymentPopover) {
        paymentPopover.style.display = 'none';
        document.body.style.overflow = 'auto';
        console.log('[openChatWindow] Payment-Popover geschlossen');
    }
    
    if (chatWindow && chatContainer && sidebar) {
        // Verstecke ALLE Kinder der Sidebar außer chatWindowContainer
        Array.from(sidebar.children).forEach(child => {
            if (child.id !== 'chatWindowContainer') {
                child.style.display = 'none';
            }
        });
        
        // Verschiebe Chat in Container
        chatContainer.appendChild(chatWindow);
        chatWindow.style.display = 'flex';
        
        // Starte Chat-Timer
        chatStartTime = Date.now();
        chatTimeInterval = setInterval(updateChatTime, 1000);
        
        // Aktiviere Mikrofon automatisch beim Chat-Start
        console.log('[openChatWindow] Aktiviere Mikrofon für Sprachkommunikation...');
        console.log('[openChatWindow] window.enableViewerMicrophone:', typeof window.enableViewerMicrophone);
        
        if (typeof window.enableViewerMicrophone === 'function') {
            setTimeout(async () => {
                console.log('[openChatWindow] Rufe enableViewerMicrophone auf...');
                try {
                    const success = await window.enableViewerMicrophone();
                    console.log('[openChatWindow] Mikrofon-Aktivierung:', success ? 'Erfolgreich' : 'Fehlgeschlagen');
                } catch (error) {
                    console.error('[openChatWindow] Fehler bei Mikrofon-Aktivierung:', error);
                }
            }, 500);
        } else {
            console.error('[openChatWindow] enableViewerMicrophone Funktion nicht gefunden!');
            console.error('[openChatWindow] Verfügbare window-Funktionen:', Object.keys(window).filter(k => k.includes('Microphone')));
        }
        
        // Fokussiere Chat-Input
        setTimeout(() => {
            const chatInput = document.getElementById('chatInput');
            if (chatInput) chatInput.focus();
        }, 300);
    }
}

function updateChatTime() {
    const chatTime = document.getElementById('chatTime');
    if (chatTime && chatStartTime) {
        const elapsed = Math.floor((Date.now() - chatStartTime) / 1000);
        const hours = Math.floor(elapsed / 3600);
        const minutes = Math.floor((elapsed % 3600) / 60);
        const seconds = elapsed % 60;
        chatTime.textContent = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    }
}

function confirmCloseChat() {
    if (confirm('Möchtest du die Chat-Session wirklich verlassen?')) {
        closeChatWindow();
    }
}

function closeChatWindow() {
    const chatWindow = document.getElementById('chatWindow');
    const sidebar = document.getElementById('sidebar');
    const paymentPopover = document.getElementById('paymentPopover');
    
    console.log('[closeChatWindow] Schließe Chat...');
    
    // Lösche Chat-Status aus localStorage
    const urlParams = new URLSearchParams(window.location.search);
    const roomId = urlParams.get('room');
    if (roomId) {
        localStorage.removeItem(`chatActive_${roomId}`);
        localStorage.removeItem(`chatStartTime_${roomId}`);
        console.log('[closeChatWindow] Chat-Status aus localStorage gelöscht');
    }
    
    // 1. Schließe Chat-Fenster
    if (chatWindow) {
        chatWindow.style.display = 'none';
        console.log('[closeChatWindow] Chat-Fenster geschlossen');
    }
    
    // 2. Deaktiviere Mikrofon
    console.log('[closeChatWindow] Deaktiviere Mikrofon...');
    if (typeof window.disableViewerMicrophone === 'function') {
        window.disableViewerMicrophone();
    }
    
    // 3. Schalte Video stumm und sperre Audio wieder
    window.audioUnlocked = false;
    const video = document.getElementById('videoPlayer');
    if (video) {
        video.muted = true;
        console.log('[closeChatWindow] Video stummgeschaltet, audioUnlocked:', window.audioUnlocked);
    }
    
    // Aktualisiere Mute-Icon (zeigt Schloss)
    if (typeof updateMuteIcon === 'function') {
        updateMuteIcon();
    }
    
    // 2. Schließe Payment-Popover SOFORT
    if (paymentPopover) {
        paymentPopover.style.display = 'none';
        document.body.style.overflow = 'auto';
        console.log('[closeChatWindow] Payment-Popover geschlossen');
    }
    
    // 3. Timer stoppen
    if (chatTimeInterval) {
        clearInterval(chatTimeInterval);
        chatTimeInterval = null;
    }
    
    // 4. Reservierung aufheben
    unreserveStream();
    
    // 5. Zeige Sidebar wieder an
    if (sidebar) {
        sidebar.style.display = 'block';
        
        // Stelle alle Kinder wieder her
        Array.from(sidebar.children).forEach(child => {
            if (child.id !== 'chatWindowContainer') {
                child.style.removeProperty('display');
            }
        });
        console.log('[closeChatWindow] Sidebar wiederhergestellt');
    }
    
    // 6. Stelle sicher, dass Payment-Popover wirklich geschlossen ist
    setTimeout(() => {
        if (paymentPopover) {
            paymentPopover.style.display = 'none';
            console.log('[closeChatWindow] Payment-Popover final geschlossen');
        }
    }, 100);
}

function showBookingConfirmation() {
    console.log('[showBookingConfirmation] Funktion gestartet');
    const confirmation = document.getElementById('bookingConfirmation');
    console.log('[showBookingConfirmation] confirmation Element:', confirmation);
    
    if (confirmation) {
        confirmation.style.display = 'block';
        console.log('[showBookingConfirmation] Bestätigung angezeigt');
        
        // Verstecke nach 5 Sekunden
        setTimeout(() => {
            confirmation.style.opacity = '0';
            confirmation.style.transition = 'opacity 0.5s';
            setTimeout(() => {
                confirmation.style.display = 'none';
                confirmation.style.opacity = '1';
            }, 500);
        }, 5000);
    }
}

function sendChatMessage() {
    const chatInput = document.getElementById('chatInput');
    const chatMessages = document.getElementById('chatMessages');
    
    if (chatInput && chatMessages && chatInput.value.trim()) {
        const message = chatInput.value.trim();
        const messageEl = document.createElement('div');
        messageEl.style.cssText = 'background: rgba(212, 175, 55, 0.1); padding: 12px 16px; border-radius: 12px; margin-bottom: 12px; border-left: 3px solid var(--accent-gold);';
        messageEl.innerHTML = `
            <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 4px;">Du • ${new Date().toLocaleTimeString('de-DE', {hour: '2-digit', minute: '2-digit'})}</div>
            <div style="color: var(--text-primary);">${message}</div>
        `;
        chatMessages.appendChild(messageEl);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        chatInput.value = '';
    }
}

function unreserveStream() {
    const urlParams = new URLSearchParams(window.location.search);
    const roomId = urlParams.get('room');
    
    if (roomId) {
        const url = `http://localhost:3000/api/reserve/${encodeURIComponent(roomId)}`;
        
        console.log(`[Unreserve] Sende DELETE für ${roomId}`);
        
        // Verwende fetch mit DELETE
        fetch(url, {
            method: 'DELETE',
            keepalive: true,
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(res => {
            console.log(`[Unreserve] Response Status: ${res.status}`);
            return res.json();
        })
        .then(data => {
            console.log(`[Unreserve] Reservierung aufgehoben für ${roomId}:`, data);
        })
        .catch(err => {
            console.error('[Unreserve] Fehler beim Aufheben der Reservierung:', err);
        });
    }
}

// Öffne Chat nach Zahlung (wird von premium-card.php aufgerufen)
window.openDriverChat = function() {
    console.log('[openDriverChat] Funktion aufgerufen');
    console.log('[openDriverChat] showBookingConfirmation:', typeof showBookingConfirmation);
    console.log('[openDriverChat] openChatWindow:', typeof openChatWindow);
    
    try {
        // Speichere Chat-Status in localStorage für Persistenz
        const urlParams = new URLSearchParams(window.location.search);
        const roomId = urlParams.get('room');
        if (roomId) {
            localStorage.setItem(`chatActive_${roomId}`, 'true');
            localStorage.setItem(`chatStartTime_${roomId}`, Date.now().toString());
            console.log('[openDriverChat] Chat-Status gespeichert in localStorage');
        }
        
        showBookingConfirmation();
        openChatWindow();
        
        // Aktiviere Ton nach Bezahlung
        window.audioUnlocked = true; // Sperre aufheben
        const video = document.getElementById('videoPlayer');
        if (video) {
            video.muted = false;
            console.log('[openDriverChat] Video-Ton aktiviert, audioUnlocked:', window.audioUnlocked);
        }
        
        // Aktualisiere Mute-Icon
        if (typeof updateMuteIcon === 'function') {
            updateMuteIcon();
        }
        
        console.log('[openDriverChat] Chat sollte jetzt geöffnet sein');
    } catch (error) {
        console.error('[openDriverChat] Fehler:', error);
    }
};

// Test: Funktion sofort nach Definition prüfen
console.log('[INIT] window.openDriverChat definiert:', typeof window.openDriverChat);

// Reservierung aufheben beim Verlassen der Seite
window.addEventListener('beforeunload', () => {
    unreserveStream();
});

// Reservierung aufheben beim Schließen des Tabs (zusätzlich)
window.addEventListener('pagehide', () => {
    unreserveStream();
});

// Prüfe beim Laden, ob Chat bereits aktiv war (nach Reload)
window.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const roomId = urlParams.get('room');
    
    if (roomId) {
        const chatActive = localStorage.getItem(`chatActive_${roomId}`);
        const savedStartTime = localStorage.getItem(`chatStartTime_${roomId}`);
        
        if (chatActive === 'true') {
            console.log('[DOMContentLoaded] Chat war aktiv - stelle wieder her');
            
            // Stelle Chat-Session wieder her
            setTimeout(() => {
                if (savedStartTime) {
                    chatStartTime = parseInt(savedStartTime);
                }
                openChatWindow();
            }, 1000);
        }
    }
});

// ESC zum Schließen
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeChatWindow();
});

// Chat-Input Enter-Handler
document.addEventListener('DOMContentLoaded', () => {
    const chatInput = document.getElementById('chatInput');
    if (chatInput) {
        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendChatMessage();
            }
        });
    }
});
