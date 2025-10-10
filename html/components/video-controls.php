<div id="customControls" style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(to top, rgba(0,0,0,0.95) 0%, rgba(0,0,0,0.7) 50%, transparent 100%); padding: 15px 30px 20px 30px; z-index: 5; opacity: 1; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); backdrop-filter: blur(10px);">
    <!-- Progress Bar Container -->
    <div style="margin-bottom: 12px; position: relative;">
        <input type="range" id="progressBar" min="0" max="100" value="0" 
               style="width: 100%; height: 5px; background: linear-gradient(to right, #d4af37 0%, #d4af37 0%, rgba(255,255,255,0.2) 0%); border-radius: 10px; cursor: pointer; -webkit-appearance: none; appearance: none; transition: height 0.2s;">
        <style>
            #progressBar::-webkit-slider-thumb {
                -webkit-appearance: none;
                appearance: none;
                width: 16px;
                height: 16px;
                border-radius: 50%;
                background: linear-gradient(135deg, #f4d03f, #d4af37);
                cursor: pointer;
                box-shadow: 0 2px 8px rgba(212, 175, 55, 0.5);
                transition: all 0.2s;
            }
            #progressBar::-webkit-slider-thumb:hover {
                transform: scale(1.2);
                box-shadow: 0 4px 12px rgba(212, 175, 55, 0.7);
            }
            #progressBar::-moz-range-thumb {
                width: 16px;
                height: 16px;
                border-radius: 50%;
                background: linear-gradient(135deg, #f4d03f, #d4af37);
                cursor: pointer;
                border: none;
                box-shadow: 0 2px 8px rgba(212, 175, 55, 0.5);
                transition: all 0.2s;
            }
            #progressBar::-moz-range-thumb:hover {
                transform: scale(1.2);
                box-shadow: 0 4px 12px rgba(212, 175, 55, 0.7);
            }
            #progressBar:hover {
                height: 7px;
            }
        </style>
    </div>
    
    <!-- Controls Row -->
    <div style="display: flex; align-items: center; gap: 20px;">
        <!-- Play/Pause Button -->
        <button id="playPauseBtn" style="background: none; border: none; color: white; font-size: 24px; cursor: pointer; padding: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">▶</button>
        
        <!-- Volume Control -->
        <div style="display: flex; align-items: center; gap: 10px; flex: 0 0 auto;">
            <button id="muteBtn" style="background: none; border: none; color: white; font-size: 20px; cursor: pointer; padding: 0; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                    <path id="volumeIcon" d="M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                </svg>
            </button>
            <input type="range" id="volumeBar" min="0" max="100" value="100" style="width: 80px; height: 4px; background: rgba(255,255,255,0.2); border-radius: 10px; cursor: pointer; -webkit-appearance: none; appearance: none;">
            <style>
                #volumeBar::-webkit-slider-thumb {
                    -webkit-appearance: none;
                    appearance: none;
                    width: 12px;
                    height: 12px;
                    border-radius: 50%;
                    background: white;
                    cursor: pointer;
                }
                #volumeBar::-moz-range-thumb {
                    width: 12px;
                    height: 12px;
                    border-radius: 50%;
                    background: white;
                    cursor: pointer;
                    border: none;
                }
            </style>
        </div>
        
        <!-- Time Display -->
        <div style="color: white; font-size: 14px; font-weight: 500; font-family: 'DM Sans', sans-serif; flex: 0 0 auto;">
            <span id="currentTime">00:00</span> / <span id="totalDuration">00:00</span>
        </div>
        
        <!-- Spacer -->
        <div style="flex: 1;"></div>
        
        <!-- Quality Badge -->
        <div id="qualityBadge" style="background: rgba(212, 175, 55, 0.2); color: #d4af37; padding: 4px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; letter-spacing: 0.5px; border: 1px solid rgba(212, 175, 55, 0.3);">HD</div>
        
        <!-- Fullscreen Button -->
        <button id="fullscreenBtn" style="background: none; border: none; color: white; font-size: 20px; cursor: pointer; padding: 0; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
            </svg>
        </button>
    </div>
</div>
