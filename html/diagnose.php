<?php
// Diagnose-Seite für System-Monitoring
$pageTitle = 'System-Diagnose';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - PLNX</title>
    <?php include 'components/head-meta.php'; ?>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@200;300;400;500;600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="components/global.css">
    <style>
        .diagnose-container {
            max-width: 1400px;
            margin: 120px auto 40px;
            padding: 0 40px;
        }

        .diagnose-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .diagnose-header h1 {
            font-size: 48px;
            font-weight: 600;
            background: linear-gradient(135deg, #d4af37 0%, #f4d03f 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 16px;
        }

        .diagnose-header p {
            color: var(--text-secondary);
            font-size: 18px;
        }

        .diagnose-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .diagnose-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px;
            transition: all 0.3s;
        }

        .diagnose-card:hover {
            border-color: var(--accent-gold);
            box-shadow: 0 8px 32px rgba(212, 175, 55, 0.1);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
        }

        .card-icon {
            width: 40px;
            height: 40px;
            background: rgba(212, 175, 55, 0.1);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .card-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .status-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .status-item:last-child {
            border-bottom: none;
        }

        .status-label {
            color: var(--text-secondary);
            font-size: 14px;
        }

        .status-value {
            color: var(--text-primary);
            font-weight: 500;
            font-family: 'IBM Plex Mono', monospace;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 100px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-ok {
            background: rgba(34, 197, 94, 0.2);
            color: #22c55e;
        }

        .status-warning {
            background: rgba(251, 191, 36, 0.2);
            color: #fbbf24;
        }

        .status-error {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }

        .refresh-btn {
            background: linear-gradient(135deg, #d4af37 0%, #f4d03f 100%);
            color: #000;
            border: none;
            padding: 12px 32px;
            border-radius: 100px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin: 20px auto;
            display: block;
        }

        .refresh-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(212, 175, 55, 0.3);
        }

        .log-output {
            background: #000;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            font-family: 'IBM Plex Mono', monospace;
            font-size: 12px;
            color: #22c55e;
            max-height: 400px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: var(--text-secondary);
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(212, 175, 55, 0.2);
            border-top-color: var(--accent-gold);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 16px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .diagnose-container {
                padding: 0 20px;
                margin-top: 100px;
            }

            .diagnose-grid {
                grid-template-columns: 1fr;
            }

            .diagnose-header h1 {
                font-size: 32px;
            }
        }
    </style>
</head>
<body>
    <?php include 'components/header.php'; ?>
    
    <div class="diagnose-container">
        <div class="diagnose-header">
            <h1>System-Diagnose</h1>
            <p>Echtzeit-Monitoring aller Streaming-Komponenten</p>
        </div>

        <button class="refresh-btn" onclick="loadDiagnose()">🔄 Aktualisieren</button>

        <div id="diagnoseContent">
            <div class="loading">
                <div class="spinner"></div>
                <p>Lade Diagnosedaten...</p>
            </div>
        </div>
    </div>

    <script>
        async function loadDiagnose() {
            const content = document.getElementById('diagnoseContent');
            content.innerHTML = `
                <div class="loading">
                    <div class="spinner"></div>
                    <p>Lade Diagnosedaten...</p>
                </div>
            `;

            try {
                const response = await fetch('/api/diagnose.php');
                const data = await response.json();

                content.innerHTML = `
                    <div class="diagnose-grid">
                        <!-- SRS Streams -->
                        <div class="diagnose-card">
                            <div class="card-header">
                                <div class="card-icon">📡</div>
                                <div class="card-title">SRS Streams</div>
                            </div>
                            <div class="status-item">
                                <span class="status-label">Aktive Streams</span>
                                <span class="status-value">${data.srs.stream_count || 0}</span>
                            </div>
                            <div class="status-item">
                                <span class="status-label">Gesamte Clients</span>
                                <span class="status-value">${data.srs.total_clients || 0}</span>
                            </div>
                            <div class="status-item">
                                <span class="status-label">Status</span>
                                <span class="status-badge ${data.srs.status === 'ok' ? 'status-ok' : 'status-error'}">
                                    ${data.srs.status === 'ok' ? '✓ Online' : '✗ Offline'}
                                </span>
                            </div>
                        </div>

                        <!-- FFmpeg Prozesse -->
                        <div class="diagnose-card">
                            <div class="card-header">
                                <div class="card-icon">🎬</div>
                                <div class="card-title">FFmpeg Prozesse</div>
                            </div>
                            <div class="status-item">
                                <span class="status-label">Laufende Prozesse</span>
                                <span class="status-value">${data.ffmpeg.process_count || 0}</span>
                            </div>
                            <div class="status-item">
                                <span class="status-label">Erwartete Prozesse</span>
                                <span class="status-value">3</span>
                            </div>
                            <div class="status-item">
                                <span class="status-label">Status</span>
                                <span class="status-badge ${data.ffmpeg.process_count === 3 ? 'status-ok' : 'status-warning'}">
                                    ${data.ffmpeg.process_count === 3 ? '✓ OK' : '⚠ Warnung'}
                                </span>
                            </div>
                        </div>

                        <!-- Container Status -->
                        <div class="diagnose-card">
                            <div class="card-header">
                                <div class="card-icon">🐳</div>
                                <div class="card-title">Docker Container</div>
                            </div>
                            ${data.containers.map(c => `
                                <div class="status-item">
                                    <span class="status-label">${c.name}</span>
                                    <span class="status-badge ${c.status === 'running' ? 'status-ok' : 'status-error'}">
                                        ${c.status === 'running' ? '✓ Running' : '✗ Stopped'}
                                    </span>
                                </div>
                            `).join('')}
                        </div>

                        <!-- System Resources -->
                        <div class="diagnose-card">
                            <div class="card-header">
                                <div class="card-icon">💻</div>
                                <div class="card-title">System-Ressourcen</div>
                            </div>
                            <div class="status-item">
                                <span class="status-label">CPU Auslastung</span>
                                <span class="status-value">${data.system.cpu || 'N/A'}</span>
                            </div>
                            <div class="status-item">
                                <span class="status-label">RAM Nutzung</span>
                                <span class="status-value">${data.system.memory || 'N/A'}</span>
                            </div>
                            <div class="status-item">
                                <span class="status-label">Disk Space</span>
                                <span class="status-value">${data.system.disk || 'N/A'}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Stream Details -->
                    <div class="diagnose-card" style="margin-top: 24px;">
                        <div class="card-header">
                            <div class="card-icon">📊</div>
                            <div class="card-title">Stream Details</div>
                        </div>
                        ${data.srs.streams ? data.srs.streams.map(s => `
                            <div style="margin-bottom: 20px; padding: 16px; background: rgba(0,0,0,0.3); border-radius: 8px;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                                    <span style="color: var(--accent-gold); font-weight: 600;">${s.name}</span>
                                    <span class="status-badge status-ok">${s.clients} Clients</span>
                                </div>
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; font-size: 13px;">
                                    <div>
                                        <div style="color: var(--text-secondary);">Video</div>
                                        <div style="color: var(--text-primary);">${s.video?.width}x${s.video?.height} ${s.video?.codec}</div>
                                    </div>
                                    <div>
                                        <div style="color: var(--text-secondary);">Audio</div>
                                        <div style="color: var(--text-primary);">${s.audio?.codec} ${s.audio?.sample_rate}Hz</div>
                                    </div>
                                    <div>
                                        <div style="color: var(--text-secondary);">Bitrate</div>
                                        <div style="color: var(--text-primary);">${s.kbps?.recv_30s || 0} kbps</div>
                                    </div>
                                </div>
                            </div>
                        `).join('') : '<p style="color: var(--text-secondary);">Keine Streams aktiv</p>'}
                    </div>

                    <!-- Raw Logs -->
                    <div class="diagnose-card" style="margin-top: 24px;">
                        <div class="card-header">
                            <div class="card-icon">📝</div>
                            <div class="card-title">System Logs</div>
                        </div>
                        <div class="log-output">${data.logs || 'Keine Logs verfügbar'}</div>
                    </div>
                `;
            } catch (error) {
                content.innerHTML = `
                    <div class="diagnose-card">
                        <div class="card-header">
                            <div class="card-icon">⚠️</div>
                            <div class="card-title">Fehler</div>
                        </div>
                        <p style="color: var(--text-secondary);">Diagnosedaten konnten nicht geladen werden: ${error.message}</p>
                    </div>
                `;
            }
        }

        // Auto-refresh alle 30 Sekunden
        setInterval(loadDiagnose, 30000);

        // Initial load
        loadDiagnose();
    </script>

    <?php include 'components/footer.php'; ?>
</body>
</html>
