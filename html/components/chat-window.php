<div id="chatWindow" style="display: none; width: 100%; background: linear-gradient(135deg, rgba(26, 26, 36, 0.98), rgba(20, 20, 28, 0.98)); border-radius: 20px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3); border: 1px solid rgba(212, 175, 55, 0.2); backdrop-filter: blur(20px); flex-direction: column; animation: slideInUp 0.3s ease-out; margin-top: 20px;">
    <!-- Chat Header -->
    <div style="padding: 20px; border-bottom: 1px solid rgba(212, 175, 55, 0.2); display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h3 style="font-family: 'IBM Plex Sans', sans-serif; font-size: 18px; font-weight: 500; color: var(--accent-gold); margin-bottom: 4px;">Live Chat</h3>
            <div style="font-size: 12px; color: var(--text-secondary);">
                <span id="chatTime">00:00:00</span> • Aktiv
            </div>
        </div>
        <button onclick="confirmCloseChat()" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #ef4444; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 500; cursor: pointer; transition: all 0.3s;" onmouseover="this.style.background='rgba(239, 68, 68, 0.2)'" onmouseout="this.style.background='rgba(239, 68, 68, 0.1)'">
            Chat schließen
        </button>
    </div>
    
    <!-- Chat Messages -->
    <div id="chatMessages" style="flex: 1; padding: 20px; overflow-y: auto; max-height: 400px; min-height: 300px;">
        <div style="text-align: center; color: var(--text-secondary); font-size: 13px; margin-bottom: 20px;">
            <div style="background: rgba(212, 175, 55, 0.1); padding: 12px; border-radius: 12px; border: 1px solid rgba(212, 175, 55, 0.2);">
                ✨ Chat-Session gestartet
            </div>
        </div>
    </div>
    
    <!-- Chat Input -->
    <div style="padding: 20px; border-top: 1px solid rgba(212, 175, 55, 0.2);">
        <div style="display: flex; gap: 12px;">
            <input type="text" id="chatInput" placeholder="Nachricht eingeben..." style="flex: 1; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(212, 175, 55, 0.2); color: white; padding: 12px 16px; border-radius: 12px; font-size: 14px; font-family: inherit; outline: none; transition: all 0.3s;" onfocus="this.style.borderColor='rgba(212, 175, 55, 0.5)'; this.style.background='rgba(255, 255, 255, 0.08)'" onblur="this.style.borderColor='rgba(212, 175, 55, 0.2)'; this.style.background='rgba(255, 255, 255, 0.05)'">
            <button onclick="sendChatMessage()" style="background: linear-gradient(135deg, var(--accent-gold), var(--accent-gold-light)); border: none; color: #000; padding: 12px 24px; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(212, 175, 55, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(212, 175, 55, 0.3)'">
                Senden
            </button>
        </div>
    </div>
</div>

<style>
    @keyframes slideInUp {
        from { transform: translateY(100px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    
    #chatMessages::-webkit-scrollbar { width: 6px; }
    #chatMessages::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.05); border-radius: 3px; }
    #chatMessages::-webkit-scrollbar-thumb { background: rgba(212, 175, 55, 0.3); border-radius: 3px; }
    #chatMessages::-webkit-scrollbar-thumb:hover { background: rgba(212, 175, 55, 0.5); }
</style>
