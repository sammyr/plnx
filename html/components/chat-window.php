<div id="chatWindow" style="display: none; width: 100%; background: rgba(15, 15, 22, 0.95); border-radius: 20px; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4); border: 1px solid rgba(255, 255, 255, 0.08); backdrop-filter: blur(20px); flex-direction: column; animation: slideInUp 0.3s ease-out; margin-top: 20px; overflow: hidden;">
    <!-- Chat Header -->
    <div style="padding: 20px 24px; background: rgba(255, 255, 255, 0.02); border-bottom: 1px solid rgba(255, 255, 255, 0.06); display: flex; justify-content: space-between; align-items: center;">
        <!-- Decorative gradient line removed for cleaner look -->
        
        <div style="display: flex; align-items: center; gap: 14px;">
            <!-- Chat Icon - Minimalistisch -->
            <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(212, 175, 55, 0.08); border: 1px solid rgba(212, 175, 55, 0.15); display: flex; align-items: center; justify-content: center;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M21 15C21 15.5304 20.7893 16.0391 20.4142 16.4142C20.0391 16.7893 19.5304 17 19 17H7L3 21V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V15Z" stroke="rgba(212, 175, 55, 0.6)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div>
                <h3 style="font-family: 'IBM Plex Sans', sans-serif; font-size: 16px; font-weight: 500; color: rgba(255, 255, 255, 0.95); margin-bottom: 3px; letter-spacing: -0.01em;">Live Chat</h3>
                <div style="font-size: 12px; color: rgba(255, 255, 255, 0.45); display: flex; align-items: center; gap: 6px;">
                    <span style="width: 5px; height: 5px; border-radius: 50%; background: rgba(16, 185, 129, 0.8);"></span>
                    <span id="chatTime" style="font-variant-numeric: tabular-nums;">00:00:00</span>
                    <span style="opacity: 0.6;">•</span>
                    <span>Aktiv</span>
                </div>
            </div>
        </div>
        
        <div style="display: flex; gap: 8px; align-items: center;">
            <!-- Mikrofon Toggle Button - Dezent -->
            <button id="micToggleBtn" onclick="toggleViewerMicrophone()" style="background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); color: rgba(255, 255, 255, 0.9); padding: 8px 14px; border-radius: 10px; font-size: 12px; font-weight: 500; cursor: pointer; transition: all 0.2s ease; display: flex; align-items: center; gap: 8px;" onmouseover="this.style.background='rgba(239, 68, 68, 0.12)'; this.style.borderColor='rgba(239, 68, 68, 0.3)'" onmouseout="this.style.background='rgba(239, 68, 68, 0.08)'; this.style.borderColor='rgba(239, 68, 68, 0.2)'">
                <span id="micIconSvg" style="width: 16px; height: 16px; display: flex; align-items: center; justify-content: center; opacity: 0.8;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 1C10.34 1 9 2.34 9 4V12C9 13.66 10.34 15 12 15C13.66 15 15 13.66 15 12V4C15 2.34 13.66 1 12 1Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M19 10V12C19 15.866 15.866 19 12 19C8.134 19 5 15.866 5 12V10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12 19V23M8 23H16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" id="micMuteLine"/>
                    </svg>
                </span>
                <span id="micStatus" style="font-size: 12px;">Mikrofon aus</span>
            </button>
            
            <!-- Close Button - Minimalistisch -->
            <button onclick="confirmCloseChat()" style="background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.1); color: rgba(255, 255, 255, 0.7); padding: 8px 14px; border-radius: 10px; font-size: 12px; font-weight: 500; cursor: pointer; transition: all 0.2s ease; display: flex; align-items: center; gap: 6px;" onmouseover="this.style.background='rgba(255, 255, 255, 0.08)'; this.style.color='rgba(255, 255, 255, 0.9)'" onmouseout="this.style.background='rgba(255, 255, 255, 0.04)'; this.style.color='rgba(255, 255, 255, 0.7)'">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Schließen</span>
            </button>
        </div>
    </div>
    
    <!-- Chat Messages -->
    <div id="chatMessages" style="flex: 1; padding: 20px; overflow-y: auto; max-height: 420px; min-height: 300px;">
        <!-- Session Started Message - Dezent -->
        <div style="text-align: center; margin-bottom: 20px;">
            <div style="background: rgba(255, 255, 255, 0.03); padding: 10px 16px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.08); display: inline-flex; align-items: center; gap: 10px;">
                <div style="width: 28px; height: 28px; border-radius: 8px; background: rgba(212, 175, 55, 0.1); display: flex; align-items: center; justify-content: center;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" fill="rgba(212, 175, 55, 0.4)" stroke="rgba(212, 175, 55, 0.6)" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <span style="color: rgba(255, 255, 255, 0.7); font-size: 13px; font-weight: 500;">Chat-Session gestartet</span>
            </div>
        </div>
        
        <!-- Voice Connection Info - Dezent -->
        <div id="microphoneInfoBox" style="text-align: center; margin-bottom: 20px;">
            <div style="background: rgba(16, 185, 129, 0.06); padding: 12px 18px; border-radius: 12px; border: 1px solid rgba(16, 185, 129, 0.15); display: inline-flex; align-items: center; gap: 12px; max-width: 90%;">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(16, 185, 129, 0.12); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 1C10.34 1 9 2.34 9 4V12C9 13.66 10.34 15 12 15C13.66 15 15 13.66 15 12V4C15 2.34 13.66 1 12 1Z" stroke="rgba(16, 185, 129, 0.8)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M19 10V12C19 15.866 15.866 19 12 19C8.134 19 5 15.866 5 12V10" stroke="rgba(16, 185, 129, 0.8)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12 19V23M8 23H16" stroke="rgba(16, 185, 129, 0.8)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div style="text-align: left;">
                    <div style="color: rgba(255, 255, 255, 0.85); font-size: 13px; font-weight: 500; margin-bottom: 2px;">Sprachverbindung aktiv</div>
                    <div style="color: rgba(255, 255, 255, 0.5); font-size: 11px;">Du kannst mit dem Streamer sprechen</div>
                </div>
            </div>
            <div style="margin-top: 10px; font-size: 11px; color: rgba(255, 255, 255, 0.4);">
                Mikrofon funktioniert nicht? 
                <button onclick="window.enableViewerMicrophone()" style="background: none; border: none; color: rgba(16, 185, 129, 0.8); text-decoration: none; cursor: pointer; font-size: 11px; font-weight: 500; padding: 2px 6px; margin-left: 2px; border-radius: 4px; transition: all 0.2s;" onmouseover="this.style.color='rgba(16, 185, 129, 1)'; this.style.textDecoration='underline'" onmouseout="this.style.color='rgba(16, 185, 129, 0.8)'; this.style.textDecoration='none'">
                    Hier klicken
                </button>
            </div>
        </div>
    </div>
    
    <!-- Chat Input - Dezent -->
    <div style="padding: 18px 20px; background: rgba(255, 255, 255, 0.02); border-top: 1px solid rgba(255, 255, 255, 0.06);">
        <div style="display: flex; gap: 10px; align-items: center;">
            <div style="flex: 1; position: relative;">
                <input type="text" id="chatInput" placeholder="Nachricht eingeben..." style="width: 100%; background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.1); color: white; padding: 11px 16px 11px 42px; border-radius: 10px; font-size: 13px; font-family: inherit; outline: none; transition: all 0.2s ease;" onfocus="this.style.borderColor='rgba(212, 175, 55, 0.3)'; this.style.background='rgba(255, 255, 255, 0.06)'" onblur="this.style.borderColor='rgba(255, 255, 255, 0.1)'; this.style.background='rgba(255, 255, 255, 0.04)'">
                <!-- Message Icon -->
                <div style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; opacity: 0.4;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M21 11.5C21.0034 12.8199 20.6951 14.1219 20.1 15.3C19.3944 16.7118 18.3098 17.8992 16.9674 18.7293C15.6251 19.5594 14.0782 19.9994 12.5 20C11.1801 20.0035 9.87812 19.6951 8.7 19.1L3 21L4.9 15.3C4.30493 14.1219 3.99656 12.8199 4 11.5C4.00061 9.92179 4.44061 8.37488 5.27072 7.03258C6.10083 5.69028 7.28825 4.6056 8.7 3.90003C9.87812 3.30496 11.1801 2.99659 12.5 3.00003H13C15.0843 3.11502 17.053 3.99479 18.5291 5.47089C20.0052 6.94699 20.885 8.91568 21 11V11.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>
            
            <!-- Send Button - Edel aber dezent -->
            <button onclick="sendChatMessage()" style="background: rgba(212, 175, 55, 0.15); border: 1px solid rgba(212, 175, 55, 0.25); color: rgba(212, 175, 55, 1); padding: 11px 20px; border-radius: 10px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; display: flex; align-items: center; gap: 6px;" onmouseover="this.style.background='rgba(212, 175, 55, 0.22)'; this.style.borderColor='rgba(212, 175, 55, 0.35)'" onmouseout="this.style.background='rgba(212, 175, 55, 0.15)'; this.style.borderColor='rgba(212, 175, 55, 0.25)'">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22 2L11 13M22 2L15 22L11 13M22 2L2 9L11 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Senden</span>
            </button>
        </div>
    </div>
</div>

<style>
/* Dezente Animationen */
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Custom Scrollbar - Minimalistisch */
#chatMessages::-webkit-scrollbar {
    width: 6px;
}

#chatMessages::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.02);
    border-radius: 3px;
}

#chatMessages::-webkit-scrollbar-thumb {
    background: rgba(212, 175, 55, 0.2);
    border-radius: 3px;
}

#chatMessages::-webkit-scrollbar-thumb:hover {
    background: rgba(212, 175, 55, 0.35);
}
</style>
