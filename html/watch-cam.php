<?php
// Lade Location-Funktionen
require_once 'components/locations.php';

// Video-Mapping (serverseitig)
$roomId = $_GET['room'] ?? 'Driver-Berlin-001';
$isWebRTC = true; // Cam-Seite: immer WebRTC

// Lade Location-Daten aus JSON-Datei
$locationData = getLocationData($roomId);
$location = $locationData ? formatLocation($locationData) : '';
$gpsLat = $locationData['lat'] ?? '';
$gpsLon = $locationData['lon'] ?? '';
$address = $locationData['address'] ?? '';
$mapLink = getMapLink($locationData);

$titles = [
    'demo_video_stream' => 'Die Totale Erinnerung',
    'demo_stingray_stream' => 'Stingray - Intro',
    'demo_subway_stream' => 'Subway',
    'demo_chuck_stream' => 'Chuck und Larry',
    'demo_horrible_boss_stream' => 'Horrible Boss'
];
$pageTitle = $titles[$roomId] ?? 'Premium Stream';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - PLNX</title>
    <?php include 'components/head-meta.php'; ?>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@200;300;400;500;600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="components/global.css">
    <link rel="stylesheet" href="components/watch-cam-styles.css">
    <link rel="stylesheet" href="components/video-controls.css">
</head>
<body>
    <?php include 'components/header.php'; ?>
    
    <div class="container">
        <div class="video-section">
            <div class="video-wrapper">
                <div class="video-container">
                    <!-- Buchungsbestätigung Overlay -->
                    <div id="bookingConfirmation" style="display: none; position: absolute; top: 20px; left: 50%; transform: translateX(-50%); background: rgba(34, 197, 94, 0.95); backdrop-filter: blur(20px); padding: 16px 32px; border-radius: 100px; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; z-index: 10; border: 1px solid rgba(34, 197, 94, 0.3); box-shadow: 0 10px 40px rgba(34, 197, 94, 0.3); animation: slideInDown 0.5s ease-out;">
                        ✓ Stream erfolgreich gebucht
                    </div>
                    
                    <!-- Loading Indicator -->
                    <div id="loadingIndicator" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; z-index: 5;">
                        <div style="width: 60px; height: 60px; border: 4px solid rgba(212, 175, 55, 0.2); border-top-color: var(--accent-gold); border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 16px;"></div>
                        <div style="color: var(--text-secondary); font-size: 14px;">Verbinde mit Stream...</div>
                    </div>
                    
                    <!-- Video Player -->
                    <video id="videoPlayer" autoplay playsinline muted></video>
                    
                    <!-- Video Overlay (LIVE Badge + Location) -->
                    <div class="video-overlay">
                        <?php if ($isWebRTC): ?>
                        <div class="status-badge">
                            <div class="status-dot"></div>
                            <span>LIVE STREAM</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Video Title / Location Overlay -->
                    <div class="title-location-overlay" style="position: absolute; top: 20px; right: 20px; background: rgba(0, 0, 0, 0.4); backdrop-filter: blur(20px); padding: 10px 20px; border-radius: 100px; border: 1px solid rgba(212, 175, 55, 0.3); z-index: 2; display: flex; align-items: center; gap: 12px;">
                        <div id="videoTitleOverlay" style="color: #d4af37; font-size: 13px; font-weight: 600; letter-spacing: 0.5px;"><?php echo strtoupper($roomId); ?></div>
                        <?php if ($location): ?>
                        <a href="<?php echo htmlspecialchars($mapLink); ?>" target="_blank" class="location-link" style="color: rgba(255, 255, 255, 0.7); font-size: 12px; font-weight: 500; border-left: 1px solid rgba(212, 175, 55, 0.3); padding-left: 12px; text-decoration: none; transition: all 0.3s;" onmouseover="this.style.color='#d4af37'" onmouseout="this.style.color='rgba(255, 255, 255, 0.7)'">
                            📍 <?php echo htmlspecialchars($location); ?>
                        </a>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Video Controls (included from separate file) -->
                    <?php include 'components/video-controls.php'; ?>
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
    
    <!-- Chat Window Template -->
    <?php include 'components/chat-window.php'; ?>
    
    <!-- Scripts -->
    <script src="components/watch-cam-webrtc.js"></script>
    <script src="components/watch-cam-chat.js"></script>
    <script src="components/video-player.js"></script>
    
    <script>
        // Location wird jetzt serverseitig geladen (locations.json)
        console.log('[Location] Geladen aus locations.json:', '<?php echo htmlspecialchars($address); ?>');
    </script>
    
    <style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @keyframes slideInDown {
            from { transform: translateX(-50%) translateY(-100px); opacity: 0; }
            to { transform: translateX(-50%) translateY(0); opacity: 1; }
        }
    </style>
    
    <?php include 'components/footer.php'; ?>
</body>
</html>
