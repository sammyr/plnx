<?php
// Video-Mapping (serverseitig)
$videoFiles = [
    'demo_video_stream' => 'ttr.m4v',
    'demo_stingray_stream' => 'StingrayIntro.mp4',
    'demo_subway_stream' => 'Subway.m4v',
    'demo_chuck_stream' => 'Chuck.und.Larry.m4v',
    'demo_horrible_boss_stream' => 'Horrible-Boss.m4v'
];

$roomId = $_GET['room'] ?? 'demo_video_stream';
$isWebRTC = false; // Movie-Seite: immer Video-Datei
$videoFile = $videoFiles[$roomId] ?? 'ttr.m4v';

// Location-Daten aus URL-Parametern
$location = $_GET['location'] ?? '';
$gpsLat = $_GET['lat'] ?? '';
$gpsLon = $_GET['lon'] ?? '';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php 
        $titles = [
            'demo_video_stream' => 'Die Totale Erinnerung',
            'demo_stingray_stream' => 'Stingray - Intro',
            'demo_subway_stream' => 'Subway',
            'demo_chuck_stream' => 'Chuck und Larry',
            'demo_horrible_boss_stream' => 'Horrible Boss'
        ];
        echo htmlspecialchars($titles[$roomId] ?? 'Premium Stream');
    ?> - PLNX</title>
    <?php include 'components/head-meta.php'; ?>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@200;300;400;500;600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    <!-- Cache-Buster: v2.0 -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg-primary: #0a0a0f;
            --bg-secondary: #13131a;
            --bg-card: #1a1a24;
            --accent-gold: #d4af37;
            --accent-gold-light: #f4d03f;
            --text-primary: #ffffff;
            --text-secondary: #a1a1aa;
            --border: rgba(212, 175, 55, 0.2);
        }

        body {
            font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Header */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            padding: 40px 40px;
            background: rgba(10, 10, 15, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            z-index: 100;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-family: 'IBM Plex Sans', sans-serif;
            font-size: 28px;
            font-weight: 400;
            background: linear-gradient(135deg, var(--accent-gold), var(--accent-gold-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.5px;
        }

        .back-btn {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text-primary);
            padding: 10px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            font-family: inherit;
        }

        .back-btn:hover {
            background: var(--bg-card);
            border-color: var(--accent-gold);
            transform: translateX(-2px);
        }

        /* Main Container */
        .container {
            display: flex;
            gap: 40px;
            padding: 20px 40px 40px;
            max-width: 1600px;
            margin: 0 auto;
        }

        /* Video Section */
        .video-section {
            flex: 1;
        }

        .video-wrapper {
            position: relative;
            background: #000;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 40px 80px -20px rgba(0, 0, 0, 0.8);
            border: 1px solid var(--border);
        }

        .video-container {
            position: relative;
            aspect-ratio: 16/9;
        }

        video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .video-overlay {
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            pointer-events: none;
        }

        .status-badge {
            background: rgba(220, 38, 38, 0.4); /* wie viewer.php */
            backdrop-filter: blur(10px);
            padding: 10px 20px;
            border-radius: 100px;
            font-size: 13px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(220, 38, 38, 0.3);
            letter-spacing: 0.08em;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #fff;
            animation: blink 1.2s infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.35; }
        }

        .video-info {
            padding: 32px;
        }

        .video-title {
            font-family: 'IBM Plex Sans', sans-serif;
            font-size: 32px;
            font-weight: 400;
            margin-bottom: 16px;
            line-height: 1.3;
        }

        .video-meta {
            display: flex;
            gap: 24px;
            color: var(--text-secondary);
            font-size: 14px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Sidebar */
        .sidebar {
            width: 400px;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .info-card {
            background: var(--bg-card);
            border-radius: 20px;
            padding: 32px;
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
        }

        .info-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--accent-gold), transparent);
        }

        .card-title {
            font-family: 'IBM Plex Sans', sans-serif;
            font-size: 20px;
            font-weight: 400;
            margin-bottom: 24px;
            color: var(--accent-gold);
        }

        /* Time Display */
        .time-display {
            text-align: center;
            margin-bottom: 24px;
        }

        .current-time {
            font-size: 48px;
            font-weight: 300;
            font-family: 'DM Sans', sans-serif;
            letter-spacing: 2px;
            margin-bottom: 8px;
            background: linear-gradient(135deg, var(--accent-gold), var(--accent-gold-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .current-date {
            font-size: 14px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--border), transparent);
            margin: 24px 0;
        }

        /* Duration */
        .duration-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .duration-label {
            font-size: 13px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .duration-value {
            font-size: 18px;
            font-weight: 500;
            font-family: 'DM Sans', sans-serif;
        }

        /* Price Display */
        .price-container {
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.1), rgba(244, 208, 63, 0.05));
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px;
            text-align: center;
        }

        .price-label {
            font-size: 12px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 12px;
        }

        .price-amount {
            font-family: 'IBM Plex Sans', sans-serif;
            font-size: 56px;
            font-weight: 400;
            background: linear-gradient(135deg, var(--accent-gold), var(--accent-gold-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 8px;
        }

        .price-currency {
            font-size: 24px;
            margin-right: 4px;
        }

        .price-period {
            font-size: 14px;
            color: var(--text-secondary);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-top: 24px;
        }

        .stat-item {
            background: var(--bg-secondary);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid var(--border);
            text-align: center;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 4px;
            background: linear-gradient(135deg, var(--accent-gold), var(--accent-gold-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            font-size: 12px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Action Button */
        .action-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, var(--accent-gold), var(--accent-gold-light));
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            color: var(--bg-primary);
            cursor: pointer;
            transition: all 0.3s;
            font-family: inherit;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px -10px rgba(212, 175, 55, 0.5);
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                max-width: 600px;
                margin: 0 auto;
            }
        }

        @media (max-width: 768px) {
            .header {
                padding: 20px;
            }

            .container {
                padding: 100px 20px 40px;
            }

            .logo {
                font-size: 24px;
            }

            .video-info {
                padding: 24px;
            }

            .video-title {
                font-size: 24px;
            }

            .info-card {
                padding: 24px;
            }

            .current-time {
                font-size: 36px;
            }

            .price-amount {
                font-size: 42px;
            }
        }
    </style>
</head>
<body>
    <?php include 'components/header.php'; ?>
    
    <div class="container">
        <!-- Video Section -->
        <div class="video-section">
            <div class="video-wrapper">
                <div class="video-container">
                    <!-- Zurück-Button Overlay -->
                    <a href="viewer.php" style="
                        position: absolute;
                        top: 20px;
                        left: 20px;
                        z-index: 10;
                        display: inline-flex;
                        align-items: center;
                        gap: 8px;
                        padding: 10px 18px;
                        background: rgba(26, 26, 36, 0.9);
                        backdrop-filter: blur(10px);
                        border: 1px solid rgba(212, 175, 55, 0.3);
                        border-radius: 10px;
                        color: #ffffff;
                        text-decoration: none;
                        font-size: 14px;
                        font-weight: 500;
                        transition: all 0.3s;
                        
                    " onmouseover="this.style.borderColor='rgba(212, 175, 55, 0.8)'; this.style.background='rgba(26, 26, 36, 1)'" onmouseout="this.style.borderColor='rgba(212, 175, 55, 0.3)'; this.style.background='rgba(26, 26, 36, 0.9)'">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 12H5M12 19l-7-7 7-7"/>
                        </svg>
                        Zurück
                    </a>
                    
                    <!-- Buchungsbestätigung Overlay -->
                    <div id="bookingConfirmation" style="
                        display: none;
                        position: absolute;
                        top: 20px;
                        left: 50%;
                        transform: translateX(-50%);
                        background: linear-gradient(135deg, rgba(16, 185, 129, 0.95), rgba(5, 150, 105, 0.95));
                        padding: 20px 40px;
                        border-radius: 16px;
                        box-shadow: 0 10px 40px rgba(16, 185, 129, 0.4);
                        border: 2px solid rgba(16, 185, 129, 0.6);
                        z-index: 100;
                        animation: slideInDown 0.5s ease-out;
                    ">
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <div style="font-size: 32px;">✅</div>
                            <div>
                                <div style="font-size: 18px; font-weight: 700; color: white; margin-bottom: 4px;">Zahlung erfolgreich!</div>
                                <div style="font-size: 14px; color: rgba(255,255,255,0.9);">Dein Driver wurde gebucht.</div>
                            </div>
                        </div>
                    </div>
                    
                    <video id="videoPlayer" preload="auto" style="background: #000;" crossorigin="anonymous" playsinline muted autoplay>
                        <!-- Video-Quelle wird dynamisch per JavaScript gesetzt -->
                        Dein Browser unterstützt das Video-Tag nicht.
                    </video>
                    <!-- Overlay Play Button (wird per JS ein-/ausgeblendet) -->
                    <button id="playButton" style="
                        position: absolute;
                        top: 50%; left: 50%; transform: translate(-50%, -50%);
                        background: rgba(0,0,0,0.6);
                        border: 1px solid rgba(212,175,55,0.6);
                        color: #f4d03f;
                        padding: 14px 18px;
                        border-radius: 50%;
                        width: 64px; height: 64px;
                        display: none;
                        align-items: center; justify-content: center;
                        font-size: 20px; font-weight: 700;
                        cursor: pointer; z-index: 6;
                        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
                    " onclick="this.style.display='none'; const v=document.getElementById('videoPlayer'); v.muted=false; v.play().catch(()=>{});">
                        ▶
                    </button>
                    <div class="video-overlay">
                        <?php if ($isWebRTC): ?>
                        <div class="status-badge"><div class="status-dot"></div><span>LIVE STREAM</span></div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Video Title Overlay (nur Titel, kein Ort) -->
                    <div style="
                        position: absolute;
                        top: 20px;
                        right: 20px;
                        background: rgba(0, 0, 0, 0.4);
                        backdrop-filter: blur(20px);
                        padding: 10px 20px;
                        border-radius: 100px;
                        border: 1px solid rgba(212, 175, 55, 0.3);
                        z-index: 2;
                    ">
                        <div id="videoTitleOverlay" style="
                            color: #d4af37;
                            font-size: 13px;
                            font-weight: 600;
                            letter-spacing: 0.5px;
                        ">Loading...</div>
                    </div>
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
                                    box-shadow: 0 0 10px rgba(212, 175, 55, 0.8), 0 0 20px rgba(212, 175, 55, 0.4);
                                    transition: all 0.2s;
                                }
                                #progressBar::-webkit-slider-thumb:hover {
                                    width: 20px;
                                    height: 20px;
                                    box-shadow: 0 0 15px rgba(212, 175, 55, 1), 0 0 30px rgba(212, 175, 55, 0.6);
                                }
                                #progressBar::-moz-range-thumb {
                                    width: 16px;
                                    height: 16px;
                                    border-radius: 50%;
                                    background: linear-gradient(135deg, #f4d03f, #d4af37);
                                    cursor: pointer;
                                    border: none;
                                    box-shadow: 0 0 10px rgba(212, 175, 55, 0.8);
                                }
                                #progressBar:hover {
                                    height: 7px;
                                }
                                #volumeBar::-webkit-slider-thumb {
                                    -webkit-appearance: none;
                                    width: 12px;
                                    height: 12px;
                                    border-radius: 50%;
                                    background: #d4af37;
                                    cursor: pointer;
                                }
                            </style>
                        </div>
                        
                        <!-- Control Buttons -->
                        <div style="display: flex; align-items: center; gap: 20px; color: white;">
                            <!-- Play/Pause Button -->
                            <button id="playPauseBtn" onclick="togglePlayPause()" style="background: linear-gradient(135deg, #d4af37, #f4d03f); border: none; color: #0a0a0f; cursor: pointer; font-size: 20px; padding: 10px 14px; border-radius: 50%; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(212, 175, 55, 0.4); transition: all 0.3s; font-weight: bold;">
                                ▶
                            </button>
                            
                            <!-- Volume Controls -->
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <button id="muteBtn" onclick="toggleMute()" style="background: none; border: none; color: #d4af37; cursor: pointer; font-size: 20px; padding: 8px; transition: all 0.2s; filter: drop-shadow(0 0 5px rgba(212, 175, 55, 0.3));">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                                        <path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                                        <path d="M19.07 4.93a10 10 0 0 1 0 14.14"></path>
                                    </svg>
                                </button>
                                <input type="range" id="volumeBar" min="0" max="100" value="100" 
                                       style="width: 80px; height: 4px; background: rgba(255,255,255,0.2); border-radius: 2px; cursor: pointer; -webkit-appearance: none;">
                            </div>
                            
                            <!-- Time Display -->
                            <span id="timeDisplay" style="font-size: 15px; color: #d4af37; font-weight: 600; margin-left: auto; font-family: 'Courier New', monospace; text-shadow: 0 0 10px rgba(212, 175, 55, 0.3);">
                                00:00 / 00:00
                            </span>
                            
                            <!-- Video Size Display (nur für Kamera-Streams) -->
                            <span id="videoSizeDisplay" style="font-size: 13px; color: rgba(255, 255, 255, 0.6); font-weight: 500; margin-left: 16px; display: none;">
                                📹 <span id="videoSizeText">0x0</span>
                            </span>
                            
                            <!-- Quality Badge / Video Resolution -->
                            <div id="qualityBadge" style="background: linear-gradient(135deg, rgba(212,175,55,0.15), rgba(244,208,63,0.1)); border: 2px solid #d4af37; color: #d4af37; padding: 6px 14px; border-radius: 8px; font-size: 13px; font-weight: 700; letter-spacing: 1px; box-shadow: 0 0 15px rgba(212, 175, 55, 0.2); transition: all 0.3s; min-width: 60px; text-align: center;">
                                HD
                            </div>
                            
                            <!-- Fullscreen Button -->
                            <button onclick="toggleFullscreen()" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(212, 175, 55, 0.3); color: #d4af37; cursor: pointer; font-size: 20px; padding: 8px 12px; border-radius: 8px; transition: all 0.3s; backdrop-filter: blur(5px);">
                                ⛶
                            </button>
                        </div>
                    </div>
                    <div id="loadingIndicator" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 10; display: none;">
                        <div style="text-align: center;">
                            <!-- Premium Spinner -->
                            <div style="
                                width: 80px;
                                height: 80px;
                                margin: 0 auto 24px;
                                position: relative;
                            ">
                                <div style="
                                    width: 100%;
                                    height: 100%;
                                    border: 4px solid rgba(212, 175, 55, 0.1);
                                    border-top: 4px solid #d4af37;
                                    border-radius: 50%;
                                    animation: spin 1s linear infinite;
                                    box-shadow: 0 0 20px rgba(212, 175, 55, 0.3);
                                "></div>
                                <div style="
                                    position: absolute;
                                    top: 50%;
                                    left: 50%;
                                    transform: translate(-50%, -50%);
                                    width: 60%;
                                    height: 60%;
                                    border: 3px solid rgba(244, 208, 63, 0.2);
                                    border-bottom: 3px solid #f4d03f;
                                    border-radius: 50%;
                                    animation: spin 1.5s linear infinite reverse;
                                "></div>
                            </div>
                            
                            <div style="
                                font-size: 18px;
                                font-weight: 600;
                                color: #d4af37;
                                margin-bottom: 8px;
                                letter-spacing: 1px;
                            ">Lädt Video...</div>
                            
                            <div id="loadingProgress" style="
                                font-size: 14px;
                                color: rgba(212, 175, 55, 0.7);
                                font-weight: 500;
                            ">0%</div>
                        </div>
                    </div>
                    
                    <style>
                        @keyframes spin {
                            0% { transform: rotate(0deg); }
                            100% { transform: rotate(360deg); }
                        }
                    </style>
                </div>
            </div>

            <!-- Info-Sektion unter dem Video -->
            <div class="video-info">
                <h2 class="video-title" id="videoTitleBelow"></h2>
                <div id="addressLine" style="margin-top: 8px; color: var(--text-secondary); font-size: 14px; display: none;">
                    📍 <span id="addressText"></span>
                </div>
            </div>
        </div>

        <!-- Sidebar (nur bei WebRTC/Kamera-Streams anzeigen) -->
        <?php if ($isWebRTC): ?>
        <div class="sidebar" id="sidebar">
            <?php include 'components/premium-card.php'; ?>
            
            <!-- Chat-Fenster wird hier eingefügt -->
            <div id="chatWindowContainer"></div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Video Player Basisvariablen
        const video = document.getElementById('videoPlayer');
        const loadingIndicator = document.getElementById('loadingIndicator');
        
        // Optional: Nur wenn Elemente existieren
        const streamDuration = document.getElementById('streamDuration');
        const totalDuration = document.getElementById('totalDuration');
        const remainingTime = document.getElementById('remainingTime');

        // WebRTC Variablen
        const HOSTNAME = window.location.hostname;
        const SIGNALING_SERVER = `ws://${HOSTNAME}:3000`;
        const ICE_SERVERS = [
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' }
        ];
        let signalingSocket = null;
        let peerConnection = null;

        // PHP-Werte in JavaScript übernehmen
        const roomId = '<?php echo htmlspecialchars($roomId); ?>';
        // Nutzer ist nicht mehr im Checkout: Reservierungs-Flag für diese Room-ID zurücksetzen
        try { sessionStorage.removeItem(`reserve:${roomId}`); } catch {}
        const isWebRTCStream = <?php echo $isWebRTC ? 'true' : 'false'; ?>;
        const videoFile = '<?php echo htmlspecialchars($videoFile); ?>';
        const streamLocation = '<?php echo htmlspecialchars($location); ?>';
        const streamGpsLat = '<?php echo htmlspecialchars($gpsLat); ?>';
        const streamGpsLon = '<?php echo htmlspecialchars($gpsLon); ?>';
        
        // Setze Titel nur wenn Element existiert
        const videoTitleElement = document.getElementById('videoTitle');
        if (videoTitleElement) {
            videoTitleElement.textContent = roomId.replace(/_/g, ' ').toUpperCase();
        }
        
        // Setze Video-Titel im Overlay
        const videoTitleOverlay = document.getElementById('videoTitleOverlay');
        
        if (videoTitleOverlay) {
            const videoTitleBelowEl = document.getElementById('videoTitleBelow');
            const titles = {
                'demo_video_stream': 'Die Totale Erinnerung',
                'demo_stingray_stream': 'Stingray - Intro',
                'demo_subway_stream': 'Subway',
                'demo_chuck_stream': 'Chuck und Larry',
                'demo_horrible_boss_stream': 'Horrible Boss'
            };
            
            // Location-Daten für Kamera-Streams (mit echten GPS-Koordinaten)
            const locations = {
                'Driver-Berlin-001': {
                    address: 'Mitte, Friedrichstraße 123',
                    gps: '52.5200° N, 13.3889° E'
                },
                'Driver-Berlin-002': {
                    address: 'Kreuzberg, Oranienstraße 45',
                    gps: '52.4995° N, 13.4197° E'
                },
                'Driver-Hamburg-001': {
                    address: 'Altona, Reeperbahn 67',
                    gps: '53.5511° N, 9.9937° E'
                },
                'Driver-München-001': {
                    address: 'Schwabing, Leopoldstraße 89',
                    gps: '48.1351° N, 11.5820° E'
                }
            };
            
            // Titel setzen
            if (titles[roomId]) {
                videoTitleOverlay.textContent = titles[roomId];
                if (videoTitleBelowEl) videoTitleBelowEl.textContent = titles[roomId];
            } else {
                videoTitleOverlay.innerHTML = `${roomId} <span id="titleVideoSize" style="font-size: 11px; opacity: 0.7; font-weight: 400;"></span>`;
                if (videoTitleBelowEl) videoTitleBelowEl.textContent = roomId;
            }
            
            // Kein Location-Overlay bei Video-Dateien
        }
        
        // Funktion um Video-Größe zu aktualisieren
        function updateVideoSize() {
            const videoSizeText = document.getElementById('videoSizeText');
            const qualityBadge = document.getElementById('qualityBadge');
            const titleVideoSize = document.getElementById('titleVideoSize');
            
            if (video.videoWidth && video.videoHeight) {
                const width = video.videoWidth;
                const height = video.videoHeight;
                const sizeMB = calculateStreamSize();
                
                // Update Video Size Display (für Kamera-Streams unten)
                if (videoSizeText) {
                    videoSizeText.textContent = `${width}x${height}${sizeMB ? ' • ' + sizeMB : ''}`;
                }
                
                // Update Title Video Size (für Kamera-Streams im Titel)
                if (titleVideoSize) {
                    titleVideoSize.textContent = `(${width}x${height})`;
                }
                
                // Update Quality Badge mit Qualitäts-Label (HD, 4K, etc.)
                if (qualityBadge) {
                    const qualityLabel = getQualityLabel(width, height);
                    qualityBadge.textContent = qualityLabel;
                    qualityBadge.title = `Video-Auflösung: ${width}x${height} Pixel (${qualityLabel})`;
                }
            }
        }
        
        // Bestimme Qualitäts-Label basierend auf Auflösung
        function getQualityLabel(width, height) {
            const pixels = width * height;
            
            // 4K und höher
            if (width >= 3840 || height >= 2160) return '4K';
            if (width >= 2560 || height >= 1440) return '2K';
            
            // Full HD
            if (width >= 1920 && height >= 1080) return 'FHD';
            if (width >= 1920 || height >= 1080) return 'FHD';
            
            // HD
            if (width >= 1280 && height >= 720) return 'HD';
            if (width >= 1280 || height >= 720) return 'HD';
            
            // SD
            if (width >= 854 || height >= 480) return 'SD';
            if (width >= 640 || height >= 480) return 'SD';
            
            // Low Quality
            return 'LQ';
        }
        
        // Berechne ungefähre Stream-Größe
        function calculateStreamSize() {
            if (!video.videoWidth || !video.videoHeight) return '';
            const pixels = video.videoWidth * video.videoHeight;
            const fps = 30; // Annahme
            const bitsPerPixel = 0.1; // Geschätzt für komprimiertes Video
            const mbps = (pixels * fps * bitsPerPixel / 1000000).toFixed(1);
            return `${mbps} Mbps`;
        }

        // Initialisierung je nach Stream-Typ starten
        if (isWebRTCStream) {
            console.log('Starte WebRTC-Stream für Raum:', roomId);
            // Für WebRTC: Verstecke Loading sofort, da Stream live ist
            setTimeout(() => {
                if (loadingIndicator) {
                    loadingIndicator.style.display = 'none';
                    console.log('Loading für WebRTC versteckt');
                }
            }, 2000);
            initWebRTCStream();
        } else {
            console.log('Starte Datei-Stream');
            initVideoFileStream();
        }

        function initVideoFileStream() {
            // Streaming-Server URLs (SRS via Domain hls.sammyrichter.de)
            // Verwende roomId statt videoFile, da FFmpeg mit roomId streamt
            const protocol = window.location.protocol === 'https:' ? 'https:' : 'http:';
            const hlsHost = window.location.protocol === 'https:' ? 'hls.sammyrichter.de' : `${HOSTNAME}:8081`;
            const hlsStreamUrl = `${protocol}//${hlsHost}/live/${roomId}.m3u8`;
            const mp4FallbackUrl = '/videos/' + videoFile;
            
            console.log('Versuche HLS-Stream:', hlsStreamUrl);
            console.log('MP4-Fallback:', mp4FallbackUrl);
            
            // Optimierungen für niedrige Latenz
            video.preload = 'auto';
            video.playsInline = true;
            
            // Prüfe ob HLS.js verfügbar und unterstützt ist
            if (typeof Hls !== 'undefined' && Hls.isSupported()) {
                console.log('Verwende HLS.js für Streaming');
                const hls = new Hls({
                    enableWorker: true,
                    lowLatencyMode: true,
                    backBufferLength: 90,
                    maxBufferLength: 30,
                    maxMaxBufferLength: 60
                });
                
                hls.loadSource(hlsStreamUrl);
                hls.attachMedia(video);
                
                hls.on(Hls.Events.MANIFEST_PARSED, () => {
                    console.log('HLS-Manifest geladen, Stream bereit');
                });
                
                hls.on(Hls.Events.ERROR, (event, data) => {
                    console.warn('HLS-Fehler:', data.type, data.details);
                    if (data.fatal) {
                        console.error('Fataler HLS-Fehler, verwende MP4-Fallback');
                        hls.destroy();
                        video.src = mp4FallbackUrl;
                        video.load();
                    }
                });
                
                // Timeout: Falls HLS nach 5 Sekunden nicht lädt, Fallback
                setTimeout(() => {
                    if (video.readyState === 0) {
                        console.warn('HLS-Stream lädt nicht, verwende MP4-Fallback');
                        hls.destroy();
                        video.src = mp4FallbackUrl;
                        video.load();
                    }
                }, 5000);
                
            } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                // Native HLS-Unterstützung (Safari)
                console.log('Native HLS-Unterstützung erkannt');
                video.src = hlsStreamUrl;
                video.load();
                
                // Fallback zu MP4 bei Fehler
                video.onerror = () => {
                    console.warn('HLS-Stream nicht verfügbar, verwende MP4-Fallback');
                    video.src = mp4FallbackUrl;
                    video.load();
                };
            } else {
                // Kein HLS-Support, verwende direkte MP4
                console.log('Kein HLS-Support, verwende direkte MP4');
                video.src = mp4FallbackUrl;
                video.load();
            }

            // Zeige Loading nur am Anfang
            if (loadingIndicator) {
                loadingIndicator.style.display = 'block';
            }
            
            video.onloadstart = () => {
                console.log('Video lädt...');
            };

            video.onloadedmetadata = () => {
                console.log('Video-Metadaten geladen, Dauer:', video.duration, 'Größe:', video.videoWidth, 'x', video.videoHeight);
                updateVideoDuration();
                updateVideoSize();
            };
            
            // Update Video-Größe auch bei loadeddata
            video.addEventListener('loadeddata', () => {
                console.log('Video-Daten geladen, Größe:', video.videoWidth, 'x', video.videoHeight);
                updateVideoSize();
            });
            
            // Update Video-Größe regelmäßig
            setInterval(() => {
                if (video.videoWidth && video.videoHeight) {
                    updateVideoSize();
                }
            }, 2000);

            video.onloadeddata = () => {
                console.log('Video-Daten geladen');
                if (loadingIndicator) {
                    loadingIndicator.style.display = 'none';
                }
                // Versuche Video zu starten
                video.play().catch(err => console.log('Autoplay:', err));
            };
            
            // Wichtig: Sobald das erste Frame gerendert wird
            video.addEventListener('loadeddata', () => {
                console.log('Erstes Frame geladen');
                if (loadingIndicator) {
                    loadingIndicator.style.display = 'none';
                }
            }, { once: true });

            video.oncanplay = () => {
                console.log('Video kann abgespielt werden - VERSTECKE LOADING');
                if (loadingIndicator) {
                    loadingIndicator.style.display = 'none';
                    console.log('Loading-Indikator versteckt (oncanplay)');
                }
                
                // Autoplay mit Ton
                video.muted = false;
                video.play().then(() => {
                    console.log('Video spielt automatisch');
                    // Nochmal sicherstellen
                    if (loadingIndicator) loadingIndicator.style.display = 'none';
                }).catch(err => {
                    console.warn('Autoplay blockiert:', err);
                    // Zeige Play-Button
                    const playButton = document.getElementById('playButton');
                    if (playButton) playButton.style.display = 'block';
                });
            };

            video.oncanplaythrough = () => {
                console.log('Video vollständig gepuffert');
                if (loadingIndicator) loadingIndicator.style.display = 'none';
            };

            video.onerror = (e) => {
                console.error('Video-Fehler:', e);
                console.error('Video src:', video.currentSrc);
                loadingIndicator.innerHTML = `
                    <div style="text-align: center; color: #ef4444;">
                        <div style="font-size: 48px; margin-bottom: 16px;">❌</div>
                        <div style="font-size: 18px;">Fehler beim Laden</div>
                        <div style="font-size: 14px; margin-top: 8px;">Bitte Seite neu laden</div>
                    </div>
                `;
            };

            video.onwaiting = () => {
                console.log('Video puffert...');
                loadingIndicator.style.display = 'block';
            };

            video.onplaying = () => {
                console.log('Video spielt');
                if (loadingIndicator) {
                    loadingIndicator.style.display = 'none';
                }
            };
            
            // Zusätzlicher Check: Verstecke Loading wenn Video tatsächlich abspielt
            const checkPlaying = setInterval(() => {
                if (!video.paused && video.currentTime > 0 && !video.ended) {
                    console.log('Video spielt - verstecke Loading');
                    if (loadingIndicator) {
                        loadingIndicator.style.display = 'none';
                    }
                    clearInterval(checkPlaying);
                }
            }, 500);
        }

        async function initWebRTCStream() {
            try {
                loadingIndicator.style.display = 'block';

                signalingSocket = new WebSocket(SIGNALING_SERVER);
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
                        case 'offer':
                            await handleOffer(data.offer);
                            break;
                        case 'ice-candidate':
                            await handleIceCandidate(data.candidate);
                            break;
                        case 'broadcaster-left':
                            showError('Der Broadcaster hat den Stream beendet.');
                            break;
                        case 'location-update':
                            // Location-Update vom Broadcaster empfangen
                            updateLocationDisplay(data.location, data.lat, data.lon);
                            break;
                    }
                };

                signalingSocket.onerror = (error) => {
                    console.error('Signaling-Fehler:', error);
                    showError('Fehler bei der Verbindung zum Signaling-Server');
                };

                signalingSocket.onclose = () => {
                    console.warn('Signaling-Verbindung getrennt');
                };
            } catch (error) {
                console.error('WebRTC Initialisierung fehlgeschlagen:', error);
                showError('WebRTC-Initialisierung fehlgeschlagen');
            }
        }

        async function handleOffer(offer) {
            console.log('Offer empfangen:', offer);

            peerConnection = new RTCPeerConnection({ iceServers: ICE_SERVERS });

            peerConnection.ontrack = (event) => {
                console.log('WebRTC-Stream empfangen!', event.streams[0]);
                video.srcObject = event.streams[0];
                
                // Verstecke Play-Button falls vorhanden
                const playButton = document.getElementById('playButton');
                if (playButton) playButton.style.display = 'none';
                
                // Update Loading-Progress falls vorhanden
                const loadingProgress = document.getElementById('loadingProgress');
                if (loadingProgress) loadingProgress.textContent = 'Verbunden';
                
                // Verstecke Loading-Indikator für WebRTC
                setTimeout(() => {
                    if (loadingIndicator) loadingIndicator.style.display = 'none';
                }, 1000);

                // Zeige Video-Größe für Kamera-Streams
                const videoSizeDisplay = document.getElementById('videoSizeDisplay');
                if (videoSizeDisplay) {
                    videoSizeDisplay.style.display = 'inline';
                }
                
                // Update Video-Größe wenn Metadaten geladen sind
                video.onloadedmetadata = () => {
                    console.log('WebRTC Video-Metadaten:', video.videoWidth, 'x', video.videoHeight);
                    updateVideoSize();
                };
                
                // Update Video-Größe sofort und dann regelmäßig (für WebRTC wichtig)
                setTimeout(() => updateVideoSize(), 500);
                const sizeUpdateInterval = setInterval(() => {
                    if (video.videoWidth && video.videoHeight) {
                        updateVideoSize();
                    } else {
                        console.log('Warte auf Video-Dimensionen...');
                    }
                }, 500);

                // Falls Autoplay blockiert ist
                video.play().catch(() => {
                    if (playButton) playButton.style.display = 'block';
                });
            };

            peerConnection.onicecandidate = (event) => {
                if (event.candidate && signalingSocket && signalingSocket.readyState === WebSocket.OPEN) {
                    signalingSocket.send(JSON.stringify({
                        type: 'ice-candidate',
                        roomId: roomId,
                        candidate: event.candidate
                    }));
                }
            };

            peerConnection.onconnectionstatechange = () => {
                console.log('Peer Connection State:', peerConnection.connectionState);
                if (peerConnection.connectionState === 'failed') {
                    showError('Verbindung fehlgeschlagen. Bitte versuche es erneut.');
                }
            };

            await peerConnection.setRemoteDescription(new RTCSessionDescription(offer));
            const answer = await peerConnection.createAnswer();
            await peerConnection.setLocalDescription(answer);

            if (signalingSocket && signalingSocket.readyState === WebSocket.OPEN) {
                signalingSocket.send(JSON.stringify({
                    type: 'answer',
                    roomId: roomId,
                    answer: answer
                }));
                console.log('Answer gesendet');
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
            loadingIndicator.innerHTML = `
                <div style="text-align: center; color: #ef4444;">
                    <div style="font-size: 48px; margin-bottom: 16px;">❌</div>
                    <div style="font-size: 18px;">${message}</div>
                </div>
            `;
            loadingIndicator.style.display = 'block';
        }
        
        // Location-Anzeige nicht benötigt bei Video-Dateien

        // Zeit-Updates
        function updateTime() {
            const now = new Date();
            
            // Aktuelle Zeit
            const currentTimeElement = document.getElementById('currentTime');
            if (currentTimeElement) {
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                currentTimeElement.textContent = `${hours}:${minutes}:${seconds}`;
            }
            
            // Datum
            const currentDateElement = document.getElementById('currentDate');
            if (currentDateElement) {
                const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                currentDateElement.textContent = now.toLocaleDateString('de-DE', options);
            }
        }

        // Stream-Dauer
        let streamStartTime = Date.now();
        function updateStreamDuration() {
            if (streamDuration) {
                const elapsed = Math.floor((Date.now() - streamStartTime) / 1000);
                const hours = Math.floor(elapsed / 3600);
                const minutes = Math.floor((elapsed % 3600) / 60);
                const seconds = elapsed % 60;
                streamDuration.textContent = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            }
        }

        // Video-Dauer - wird angezeigt sobald Metadaten geladen sind
        video.onloadedmetadata = () => {
            console.log('Video-Metadaten geladen');
            const duration = video.duration;
            
            if (totalDuration) {
                if (duration && !isNaN(duration) && isFinite(duration)) {
                    const mins = Math.floor(duration / 60);
                    const secs = Math.floor(duration % 60);
                    totalDuration.textContent = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
                    console.log('Gesamtdauer:', totalDuration.textContent);
                } else {
                    // HLS-Streams haben oft keine Dauer
                    totalDuration.textContent = 'LIVE';
                    console.log('Dauer nicht verfügbar (HLS-Stream)');
                }
            }
            
            // Starte Video automatisch
            video.play().then(() => {
                console.log('Video spielt ab');
            }).catch(e => {
                console.log('Autoplay blockiert, Benutzer muss Play klicken:', e);
            });
        };

        // Zusätzlicher Event wenn Dauer verfügbar wird
        video.ondurationchange = () => {
            console.log('Dauer geändert:', video.duration);
            if (video.duration && !isNaN(video.duration) && isFinite(video.duration) && totalDuration) {
                const mins = Math.floor(video.duration / 60);
                const secs = Math.floor(video.duration % 60);
                totalDuration.textContent = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
            }
        };

        video.ontimeupdate = () => {
            if (video.duration && !isNaN(video.duration) && remainingTime) {
                const remaining = video.duration - video.currentTime;
                const mins = Math.floor(remaining / 60);
                const secs = Math.floor(remaining % 60);
                remainingTime.textContent = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
            }
        };

        // Zuschauer-Simulation
        setInterval(() => {
            const viewerElement = document.getElementById('viewerCount');
            if (viewerElement) {
                const viewers = 1200 + Math.floor(Math.random() * 100);
                viewerElement.textContent = viewers.toLocaleString('de-DE');
            }
        }, 5000);

        // Updates starten
        updateTime();
        setInterval(updateTime, 1000);
        setInterval(updateStreamDuration, 1000);

        // Custom Video Controls Setup
        const customControls = document.getElementById('customControls');
        const playPauseBtn = document.getElementById('playPauseBtn');
        const muteBtn = document.getElementById('muteBtn');
        const progressBar = document.getElementById('progressBar');
        const volumeBar = document.getElementById('volumeBar');
        const timeDisplay = document.getElementById('timeDisplay');
        const videoContainer = document.querySelector('.video-container');

        // Lautstärke aus localStorage laden oder Standard 20%
        const savedVolume = localStorage.getItem('plnx_volume');
        const initialVolume = savedVolume !== null ? parseFloat(savedVolume) : 0.2;
        video.volume = initialVolume;
        volumeBar.value = initialVolume * 100;
        
        // Update Mute Button basierend auf Lautstärke
        if (initialVolume === 0) {
            video.muted = true;
            muteBtn.innerHTML = `
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                    <line x1="23" y1="9" x2="17" y2="15"></line>
                    <line x1="17" y1="9" x2="23" y2="15"></line>
                </svg>
            `;
        } else {
            video.muted = false;
        }
        
        console.log('Lautstärke geladen:', Math.round(initialVolume * 100) + '%');

        // Show/Hide Controls (bleiben länger sichtbar)
        let hideControlsTimeout;
        videoContainer.addEventListener('mouseenter', () => {
            customControls.style.opacity = '1';
        });
        videoContainer.addEventListener('mouseleave', () => {
            if (!video.paused) {
                hideControlsTimeout = setTimeout(() => {
                    customControls.style.opacity = '0.3';
                }, 2000);
            }
        });
        videoContainer.addEventListener('mousemove', () => {
            customControls.style.opacity = '1';
            clearTimeout(hideControlsTimeout);
            if (!video.paused) {
                hideControlsTimeout = setTimeout(() => {
                    customControls.style.opacity = '0.3';
                }, 3000);
            }
        });

        // Play/Pause
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
        
        function toggleMute() {
            if (video.muted) {
                // Unmute: Stelle vorherige Lautstärke wieder her
                video.muted = false;
                video.volume = volumeBeforeMute > 0 ? volumeBeforeMute : 0.2;
                volumeBar.value = video.volume * 100;
                localStorage.setItem('plnx_volume', video.volume);
            } else {
                // Mute: Speichere aktuelle Lautstärke und mute
                volumeBeforeMute = video.volume;
                video.muted = true;
                localStorage.setItem('plnx_volume_before_mute', volumeBeforeMute);
            }
        }

        // Volume Control
        volumeBar.addEventListener('input', (e) => {
            const newVolume = e.target.value / 100;
            video.volume = newVolume;
            video.muted = e.target.value == 0;
            
            // Speichere Lautstärke in localStorage
            localStorage.setItem('plnx_volume', newVolume);
            console.log('Lautstärke gespeichert:', Math.round(newVolume * 100) + '%');
            
            // Update Icon
            if (video.muted || e.target.value == 0) {
                muteBtn.innerHTML = `
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                        <line x1="23" y1="9" x2="17" y2="15"></line>
                        <line x1="17" y1="9" x2="23" y2="15"></line>
                    </svg>
                `;
            } else {
                muteBtn.innerHTML = `
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                        <path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                        <path d="M19.07 4.93a10 10 0 0 1 0 14.14"></path>
                    </svg>
                `;
            }
        });

        // Update Play/Pause Button when video state changes
        video.addEventListener('play', () => {
            playPauseBtn.textContent = '⏸';
        });
        
        video.addEventListener('pause', () => {
            playPauseBtn.textContent = '▶';
        });

        // Progress Bar
        video.addEventListener('timeupdate', () => {
            if (video.duration && !isNaN(video.duration) && isFinite(video.duration)) {
                const progress = (video.currentTime / video.duration) * 100;
                progressBar.value = progress;
                progressBar.style.background = `linear-gradient(to right, #d4af37 0%, #d4af37 ${progress}%, rgba(255,255,255,0.2) ${progress}%)`;
                
                const current = formatTime(video.currentTime);
                const total = formatTime(video.duration);
                timeDisplay.textContent = `${current} / ${total}`;
            } else {
                // HLS-Stream ohne Dauer - zeige nur aktuelle Zeit
                const current = formatTime(video.currentTime);
                timeDisplay.textContent = `${current} / LIVE`;
            }
        });

        progressBar.addEventListener('input', (e) => {
            if (video.duration) {
                video.currentTime = (e.target.value / 100) * video.duration;
            }
        });

        // Format Time
        function formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        }

        // Fullscreen
        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                videoContainer.requestFullscreen();
            } else {
                document.exitFullscreen();
            }
        }

        // Quality Toggle (Placeholder)

// Keyboard Shortcuts
document.addEventListener('keydown', (e) => {
    // Ignoriere Shortcuts wenn Input/Textarea fokussiert ist
    const activeElement = document.activeElement;
    if (activeElement && (activeElement.tagName === 'INPUT' || activeElement.tagName === 'TEXTAREA')) {
        return; // Lasse normale Tastatureingabe zu
    }
    
    if (e.code === 'Space') {
        e.preventDefault();
        togglePlayPause();
    } else if (e.code === 'KeyM') {
        toggleMute();
    }
    // KeyF für Fullscreen deaktiviert
});

        // Debug: Zeige Video-Quelle
        console.log('Video-Quelle:', video.currentSrc || video.src);
        console.log('Video bereit:', video.readyState);
    </script>
    
    <!-- Chat-Fenster (in Sidebar) -->
    <div id="chatWindow" style="display: none; width: 100%; background: linear-gradient(135deg, rgba(26, 26, 36, 0.98), rgba(20, 20, 28, 0.98)); border-radius: 20px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3); border: 1px solid rgba(212, 175, 55, 0.2); backdrop-filter: blur(20px); flex-direction: column; animation: slideInUp 0.3s ease-out; margin-top: 20px;">
        <!-- Chat Header mit Uhrzeit -->
        <div style="padding: 16px 20px 12px 20px; border-bottom: 1px solid rgba(212, 175, 55, 0.1);">
            <!-- Uhrzeit oben -->
            <div id="chatTime" style="
                text-align: center; 
                font-size: 16px; 
                font-weight: 600;
                color: #d4af37; 
                margin-bottom: 12px; 
                font-family: 'Courier New', monospace;
                letter-spacing: 2px;
                text-shadow: 0 0 10px rgba(212, 175, 55, 0.3);
            ">00:00:00</div>
            
            <!-- Header -->
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: #d4af37; display: flex; align-items: center; gap: 8px;">
                        💬 Chat mit Driver
                        <span style="width: 8px; height: 8px; background: #10b981; border-radius: 50%; box-shadow: 0 0 8px rgba(16, 185, 129, 0.6); animation: pulse 2s infinite;"></span>
                    </h3>
                    <p style="margin: 4px 0 0 0; font-size: 12px; color: rgba(255,255,255,0.5);">Online • Antwortet schnell</p>
                </div>
                <button onclick="confirmCloseChat()" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: white; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; font-size: 18px; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" onmouseover="this.style.background='rgba(239, 68, 68, 0.2)'; this.style.borderColor='rgba(239, 68, 68, 0.4)'" onmouseout="this.style.background='rgba(255, 255, 255, 0.05)'; this.style.borderColor='rgba(255, 255, 255, 0.1)'">×</button>
            </div>
        </div>
        
        <!-- Chat Messages -->
        <div id="chatMessages" style="flex: 1; padding: 20px; overflow-y: auto; max-height: 400px; min-height: 300px;">
            <div style="background: rgba(99, 102, 241, 0.1); padding: 12px 16px; border-radius: 12px; margin-bottom: 8px; border-left: 3px solid #6366f1;">
                <div style="font-size: 11px; color: rgba(255,255,255,0.5); margin-bottom: 4px;">Driver • Jetzt</div>
                <div style="color: white;">Hallo! Ich bin dein Driver. Wie kann ich dir helfen?</div>
            </div>
        </div>
        
        <!-- Chat Input -->
        <div style="padding: 16px 20px; border-top: 1px solid rgba(212, 175, 55, 0.1); display: flex; gap: 12px;">
            <input type="text" id="chatInput" placeholder="Nachricht schreiben..." style="flex: 1; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(212, 175, 55, 0.2); border-radius: 12px; padding: 12px 16px; color: white; font-size: 14px; outline: none; transition: all 0.2s;" onfocus="this.style.borderColor='rgba(212, 175, 55, 0.5)'; this.style.background='rgba(255, 255, 255, 0.08)'" onblur="this.style.borderColor='rgba(212, 175, 55, 0.2)'; this.style.background='rgba(255, 255, 255, 0.05)'">
            <button onclick="sendChatMessage()" style="background: linear-gradient(135deg, #d4af37, #f4d03f); border: none; color: #1a1a24; width: 44px; height: 44px; border-radius: 12px; cursor: pointer; font-size: 20px; display: flex; align-items: center; justify-content: center; transition: all 0.2s; box-shadow: 0 4px 15px rgba(212, 175, 55, 0.4);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(212, 175, 55, 0.6)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(212, 175, 55, 0.4)'">➤</button>
        </div>
    </div>
    
    <style>
        @keyframes slideInUp {
            from { transform: translateY(100px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        @keyframes slideInDown {
            from { transform: translateX(-50%) translateY(-100px); opacity: 0; }
            to { transform: translateX(-50%) translateY(0); opacity: 1; }
        }
        #chatMessages::-webkit-scrollbar { width: 6px; }
        #chatMessages::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.05); border-radius: 3px; }
        #chatMessages::-webkit-scrollbar-thumb { background: rgba(212, 175, 55, 0.3); border-radius: 3px; }
        #chatMessages::-webkit-scrollbar-thumb:hover { background: rgba(212, 175, 55, 0.5); }
    </style>
    
    <script>
        // Chat-Funktionen
        let chatTimeInterval = null;
        let chatStartTime = null;
        
        function openChatWindow() {
            // Verschiebe Chat in Sidebar
            const chatWindow = document.getElementById('chatWindow');
            const chatContainer = document.getElementById('chatWindowContainer');
            const sidebar = document.getElementById('sidebar');
            
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
                
                // Starte Timer (nicht Uhrzeit)
                chatStartTime = Date.now();
                updateChatTime();
                chatTimeInterval = setInterval(updateChatTime, 1000);
                
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
            
            // Stoppe Timer
            if (chatTimeInterval) {
                clearInterval(chatTimeInterval);
                chatTimeInterval = null;
            }
            chatStartTime = null;
            
            if (chatWindow) {
                chatWindow.style.display = 'none';
            }
            
            // Schließe Payment-Popover IMMER (egal ob sichtbar oder nicht)
            const paymentPopover = document.getElementById('paymentPopover');
            if (paymentPopover) {
                paymentPopover.style.display = 'none';
                paymentPopover.style.visibility = 'hidden';
                paymentPopover.style.opacity = '0';
                paymentPopover.style.pointerEvents = 'none';
                console.log('Payment-Popover geschlossen');
            }
            
            // Stelle Body-Scroll wieder her
            document.body.style.overflow = 'auto';
            document.body.style.position = '';
            
            // Entferne alle Overlays
            const overlays = document.querySelectorAll('[style*="position: fixed"]');
            overlays.forEach(overlay => {
                if (overlay.id === 'paymentPopover') {
                    overlay.style.display = 'none';
                }
            });
            
            // Zeige alle Sidebar-Elemente wieder (inkl. Premium Card)
            if (sidebar) {
                // Warte kurz, damit Popover geschlossen ist
                setTimeout(() => {
                    Array.from(sidebar.children).forEach(child => {
                        if (child.id !== 'chatWindowContainer') {
                            // Entferne Inline-Style komplett
                            child.style.removeProperty('display');
                        }
                    });
                    console.log('Chat geschlossen - Premium Card wiederhergestellt');
                }, 100);
            }
        }
        
        // Zeige Buchungsbestätigung
        function showBookingConfirmation() {
            const confirmation = document.getElementById('bookingConfirmation');
            if (confirmation) {
                confirmation.style.display = 'block';
                
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
                messageEl.style.cssText = 'background: rgba(212, 175, 55, 0.1); padding: 12px 16px; border-radius: 12px; margin-bottom: 8px; border-left: 3px solid #d4af37;';
                messageEl.innerHTML = `<div style="font-size: 11px; color: rgba(255,255,255,0.5); margin-bottom: 4px;">Du • ${new Date().toLocaleTimeString('de-DE', {hour: '2-digit', minute: '2-digit'})}</div><div style="color: white;">${escapeHtml(message)}</div>`;
                chatMessages.appendChild(messageEl);
                chatMessages.scrollTop = chatMessages.scrollHeight;
                chatInput.value = '';
                
                setTimeout(() => {
                    const driverMessage = document.createElement('div');
                    driverMessage.style.cssText = 'background: rgba(99, 102, 241, 0.1); padding: 12px 16px; border-radius: 12px; margin-bottom: 8px; border-left: 3px solid #6366f1;';
                    driverMessage.innerHTML = `<div style="font-size: 11px; color: rgba(255,255,255,0.5); margin-bottom: 4px;">Driver • ${new Date().toLocaleTimeString('de-DE', {hour: '2-digit', minute: '2-digit'})}</div><div style="color: white;">Nachricht empfangen! Ich bin gleich bei dir.</div>`;
                    chatMessages.appendChild(driverMessage);
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }, 1500);
            }
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Enter-Taste zum Senden
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
        
        // ESC zum Schließen
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeChatWindow();
        });
        
        // Öffne Chat nach Zahlung (wird von premium-card.php aufgerufen)
        window.openDriverChat = function() {
            showBookingConfirmation();
            openChatWindow();
        };
    </script>
    
    <?php include 'components/footer.php'; ?>
</body>
</html>
