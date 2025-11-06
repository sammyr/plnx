<?php
// Diagnose-Seite für System-Monitoring
$pageTitle = 'System Diagnostics';
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
        body {
            background: linear-gradient(135deg, #0a0a0f 0%, #1a1a24 100%);
        }

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
            font-weight: 300;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #d4af37 0%, #f4d03f 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 16px;
        }

        .diagnose-header p {
            color: var(--text-secondary);
            font-size: 16px;
            font-weight: 300;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        .diagnose-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .diagnose-card {
            background: rgba(26, 26, 36, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(212, 175, 55, 0.1);
            border-radius: 20px;
            padding: 28px;
            transition: all 0.3s;
        }

        .diagnose-card:hover {
            /* Hover-Effekt entfernt */
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
            position: relative;
        }
        
        .restart-btn {
            margin-left: auto;
            background: rgba(212, 175, 55, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 8px;
            padding: 8px 10px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }
        
        .restart-btn:hover {
            background: rgba(212, 175, 55, 0.25);
            border-color: var(--accent-gold);
            transform: rotate(180deg);
        }

        .card-icon {
            width: 36px;
            height: 36px;
            background: rgba(212, 175, 55, 0.1);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .card-icon svg {
            width: 18px;
            height: 18px;
        }

        .card-title {
            font-size: 18px;
            font-weight: 400;
            letter-spacing: 0.01em;
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
            font-size: 13px;
            font-weight: 300;
            letter-spacing: 0.02em;
        }

        .status-value {
            color: var(--text-primary);
            font-weight: 400;
            font-family: 'IBM Plex Mono', monospace;
            font-size: 14px;
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
            background: #d4af37;
            color: #000;
            border: none;
            padding: 14px 36px;
            border-radius: 100px;
            font-weight: 500;
            font-size: 14px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
            margin: 20px auto;
            display: block;
        }

        .refresh-btn:hover {
            transform: translateY(-2px);
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
            <h1 class="diagnose-title">System Diagnostics</h1>
            <p class="diagnose-subtitle">Real-time monitoring of all streaming components</p>
        </div>

        <button class="refresh-btn" onclick="loadDiagnose()">Refresh</button>

        <div id="diagnoseContent">
            <div class="loading">
                <div class="spinner"></div>
                <p>Loading diagnostic data...</p>
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
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                console.log('Diagnose-Daten geladen:', data);

                content.innerHTML = `
                    <div class="diagnose-grid">
                        <!-- SRS Streams -->
                        <div class="diagnose-card">
                            <div class="card-header">
                                <div class="card-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="2"/>
                                        <path d="M16.24 7.76a6 6 0 0 1 0 8.49m-8.48-.01a6 6 0 0 1 0-8.49m11.31-2.82a10 10 0 0 1 0 14.14m-14.14 0a10 10 0 0 1 0-14.14"/>
                                    </svg>
                                </div>
                                <div class="card-title">SRS Streams</div>
                            </div>
                            <div class="status-item">
                                <span class="status-label">Active Streams</span>
                                <span class="status-value">${data.srs.stream_count || 0}</span>
                            </div>
                            <div class="status-item">
                                <span class="status-label">Total Clients</span>
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
                                <div class="card-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polygon points="5 3 19 12 5 21 5 3"/>
                                    </svg>
                                </div>
                                <div class="card-title">FFmpeg Processes</div>
                                <button onclick="restartStreams()" class="restart-btn" title="Restart streams">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="23 4 23 10 17 10"/>
                                        <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="status-item">
                                <span class="status-label">Running Processes</span>
                                <span class="status-value">${data.ffmpeg.process_count || 0}</span>
                            </div>
                            <div class="status-item">
                                <span class="status-label">Expected Processes</span>
                                <span class="status-value">3</span>
                            </div>
                            <div class="status-item">
                                <span class="status-label">Status</span>
                                <span class="status-badge ${data.ffmpeg.process_count === 3 ? 'status-ok' : 'status-warning'}">
                                    ${data.ffmpeg.process_count === 3 ? '✓ OK' : '⚠ Warning'}
                                </span>
                            </div>
                        </div>

                        <!-- Container Status -->
                        <div class="diagnose-card">
                            <div class="card-header">
                                <div class="card-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                                        <line x1="12" y1="22.08" x2="12" y2="12"/>
                                    </svg>
                                </div>
                                <div class="card-title">Docker Containers</div>
                            </div>
                            ${data.containers.map(c => `
                                <div class="status-item">
                                    <span class="status-label" title="${c.full_name || c.name}">${c.name}</span>
                                    <span class="status-badge ${c.status === 'running' ? 'status-ok' : c.status === 'unknown' ? 'status-warning' : 'status-error'}">
                                        ${c.status === 'running' ? '✓ Running' : c.status === 'unknown' ? '? Unknown' : '✗ Stopped'}
                                    </span>
                                </div>
                                ${c.debug ? `<div style="font-size: 11px; color: #888; padding: 8px; background: rgba(0,0,0,0.3); border-radius: 4px; margin-top: 8px; overflow-x: auto;"><pre style="margin: 0;">${c.debug}</pre></div>` : ''}
                            `).join('')}
                        </div>

                        <!-- System Resources -->
                        <div class="diagnose-card">
                            <div class="card-header">
                                <div class="card-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                                        <line x1="8" y1="21" x2="16" y2="21"/>
                                        <line x1="12" y1="17" x2="12" y2="21"/>
                                    </svg>
                                </div>
                                <div class="card-title">System Resources</div>
                            </div>
                            <div class="status-item">
                                <span class="status-label">CPU Usage</span>
                                <span class="status-value">${data.system.cpu || 'N/A'}</span>
                            </div>
                            <div class="status-item">
                                <span class="status-label">RAM Usage</span>
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
                            <div class="card-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" y1="20" x2="18" y2="10"/>
                                    <line x1="12" y1="20" x2="12" y2="4"/>
                                    <line x1="6" y1="20" x2="6" y2="14"/>
                                </svg>
                            </div>
                            <div class="card-title">Stream Details</div>
                        </div>
                        ${data.srs.streams ? data.srs.streams.map(s => {
                            const videoWidth = s.video?.width || s.publish?.video?.width;
                            const videoHeight = s.video?.height || s.publish?.video?.height;
                            const videoCodec = s.video?.codec || s.publish?.video?.codec;
                            const audioCodec = s.audio?.codec || s.publish?.audio?.codec;
                            const audioRate = s.audio?.sample_rate || s.publish?.audio?.sample_rate;
                            const bitrate = s.kbps?.recv_30s || s.publish?.kbps || s.kbps?.send_30s;
                            
                            return `
                            <div style="margin-bottom: 20px; padding: 16px; background: rgba(0,0,0,0.3); border-radius: 8px;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                                    <span style="color: var(--accent-gold); font-weight: 600;">${s.name || 'Unknown Stream'}</span>
                                    <span class="status-badge status-ok">${s.clients || 0} CLIENTS</span>
                                </div>
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; font-size: 13px;">
                                    <div>
                                        <div style="color: var(--text-secondary);">Video</div>
                                        <div style="color: var(--text-primary);">${videoWidth && videoHeight ? videoWidth + 'x' + videoHeight : 'N/A'} ${videoCodec || 'N/A'}</div>
                                    </div>
                                    <div>
                                        <div style="color: var(--text-secondary);">Audio</div>
                                        <div style="color: var(--text-primary);">${audioCodec || 'N/A'} ${audioRate ? audioRate + 'Hz' : 'N/A'}</div>
                                    </div>
                                    <div>
                                        <div style="color: var(--text-secondary);">Bitrate</div>
                                        <div style="color: var(--text-primary);">${bitrate || 0} kbps</div>
                                    </div>
                                </div>
                            </div>
                            `;
                        }).join('') : '<p style="color: var(--text-secondary);">No active streams</p>'}
                    </div>

                    <!-- Raw Logs -->
                    <div class="diagnose-card" style="margin-top: 24px;">
                        <div class="card-header">
                            <div class="card-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                    <line x1="16" y1="13" x2="8" y2="13"/>
                                    <line x1="16" y1="17" x2="8" y2="17"/>
                                    <polyline points="10 9 9 9 8 9"/>
                                </svg>
                            </div>
                            <div class="card-title">System Logs</div>
                        </div>
                        <div class="log-output">${data.logs || 'No logs available'}</div>
                    </div>
                `;
            } catch (error) {
                console.error('Error loading diagnostic data:', error);
                content.innerHTML = `
                    <div class="diagnose-card">
                        <div class="card-header">
                            <div class="card-icon">⚠️</div>
                            <div class="card-title">Error</div>
                        </div>
                        <p style="color: var(--text-secondary); margin-bottom: 12px;">Failed to load diagnostic data: ${error.message}</p>
                        <p style="color: var(--text-secondary); font-size: 13px;">Note: The API must be running on the server. Mock data is displayed locally.</p>
                        <button onclick="loadDiagnose()" style="margin-top: 16px; padding: 8px 16px; background: rgba(212, 175, 55, 0.2); border: 1px solid rgba(212, 175, 55, 0.3); border-radius: 8px; color: var(--accent-gold); cursor: pointer;">Retry</button>
                    </div>
                `;
            }
        }

        // Auto-refresh alle 30 Sekunden
        setInterval(loadDiagnose, 30000);

        // Initial load
        loadDiagnose();

        // Restart streams
        async function restartStreams() {
            if (!confirm('Do you really want to restart all streams?')) {
                return;
            }

            try {
                const response = await fetch('/api/restart-streams.php', {
                    method: 'POST'
                });
                const result = await response.json();
                
                if (result.success) {
                    alert('✓ ' + result.message);
                    // Reload nach 2 Sekunden
                    setTimeout(() => loadDiagnose(), 2000);
                } else {
                    let errorMsg = '✗ Fehler: ' + result.error;
                    if (result.debug) {
                        errorMsg += '\n\nDebug:\n' + JSON.stringify(result.debug, null, 2);
                    }
                    if (result.output) {
                        errorMsg += '\n\nOutput:\n' + result.output;
                    }
                    if (result.return_code) {
                        errorMsg += '\n\nReturn Code: ' + result.return_code;
                    }
                    alert(errorMsg);
                    console.error('Restart error:', result);
                }
            } catch (error) {
                alert('✗ Fehler: ' + error.message);
                console.error('Restart exception:', error);
            }
        }
    </script>

    <?php include 'components/footer.php'; ?>
</body>
</html>
