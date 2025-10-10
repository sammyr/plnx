#!/usr/bin/env node
/**
 * Vollautomatischer Preview-Generator
 * - Erkennt automatisch alle aktiven Streams
 * - Erzeugt Thumbnails aus dem Preview-Stream (_preview.flv)
 * - Aktualisiert Thumbnails alle 30 Sekunden
 * - Löscht alte Thumbnails wenn Stream offline
 */

const { spawn } = require('child_process');
const fs = require('fs');
const path = require('path');
const http = require('http');

const SIGNALING_ROOMS_URL = 'http://localhost:3000/rooms';
const PROJECT_ROOT = path.resolve(__dirname, '..');
const HTML_DIR = path.join(PROJECT_ROOT, 'html');
const VIDEOS_DIR = path.join(HTML_DIR, 'videos');
const THUMBS_DIR = path.join(HTML_DIR, 'thumbnails');
const UPDATE_INTERVAL = 30000; // 30 Sekunden
const THUMBNAIL_MAX_AGE = 60000; // 60 Sekunden

ensureDir(THUMBS_DIR);

function ensureDir(dir) {
  if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
}

function httpGetJson(url) {
  return new Promise((resolve, reject) => {
    const req = http.get(url, res => {
      let data = '';
      res.on('data', chunk => (data += chunk));
      res.on('end', () => {
        try {
          resolve(JSON.parse(data || '{}'));
        } catch (e) {
          reject(e);
        }
      });
    });
    req.on('error', reject);
    req.setTimeout(5000, () => {
      req.abort();
      reject(new Error('timeout'));
    });
  });
}

function httpHead(url) {
  return new Promise((resolve, reject) => {
    const req = http.request(url, { method: 'HEAD' }, res => {
      resolve({ statusCode: res.statusCode });
    });
    req.on('error', reject);
    req.setTimeout(3000, () => {
      req.abort();
      reject(new Error('timeout'));
    });
    req.end();
  });
}

function ffmpegClip(input, output, seconds = 4, scale = '480:-1') {
  return new Promise((resolve, reject) => {
    const args = [
      '-y',
      '-t', String(seconds),
      '-i', input,
      '-vf', `scale=${scale}`,
      '-an',
      '-movflags', 'faststart',
      '-c:v', 'libx264',
      '-preset', 'ultrafast',
      output
    ];
    const proc = spawn('ffmpeg', args, { stdio: 'ignore' });
    proc.on('error', reject);
    
    const timeout = setTimeout(() => {
      proc.kill();
      reject(new Error('ffmpeg timeout'));
    }, 15000);
    
    proc.on('close', code => {
      clearTimeout(timeout);
      if (code === 0) resolve();
      else reject(new Error(`ffmpeg exit ${code}`));
    });
  });
}

async function ensureDemoPreview() {
  const src = path.join(VIDEOS_DIR, 'ttr.m4v');
  const out = path.join(VIDEOS_DIR, 'ttr_preview.mp4');
  if (!fs.existsSync(src)) return;
  try {
    const stat = fs.existsSync(out) ? fs.statSync(out) : null;
    if (!stat || stat.size < 100000) {
      console.log('[preview] Generiere Demo-Preview ttr_preview.mp4');
      await ffmpegClip(src, out, 4);
      console.log('[preview] ✓ Demo-Preview erstellt');
    }
  } catch (e) {
    console.warn('[preview] Demo-Preview fehlgeschlagen:', e.message);
  }
}

async function generateRoomPreview(roomId, force = false) {
  const out = path.join(THUMBS_DIR, `${roomId}.mp4`);
  
  // Prüfe ob Thumbnail aktuell genug ist (außer force=true)
  if (!force && fs.existsSync(out)) {
    const stat = fs.statSync(out);
    const age = Date.now() - stat.mtimeMs;
    if (age < THUMBNAIL_MAX_AGE && stat.size > 50000) {
      return; // Thumbnail ist aktuell
    }
  }
  
  // Versuche zuerst Preview-Stream (_preview.flv)
  const previewUrl = `http://localhost:8080/live/${encodeURIComponent(roomId)}_preview.flv`;
  const mainUrl = `http://localhost:8080/live/${encodeURIComponent(roomId)}.flv`;
  
  console.log(`[preview] Aktualisiere Thumbnail für ${roomId}...`);
  
  try {
    // Prüfe Preview-Stream
    const head = await httpHead(previewUrl).catch(() => ({ statusCode: 0 }));
    if (head.statusCode && head.statusCode >= 200 && head.statusCode < 400) {
      await ffmpegClip(previewUrl, out, 4);
      console.log(`[preview] ✓ ${roomId} aus Preview-Stream`);
      return;
    }
    
    // Fallback: Hauptstream
    const head2 = await httpHead(mainUrl).catch(() => ({ statusCode: 0 }));
    if (head2.statusCode && head2.statusCode >= 200 && head2.statusCode < 400) {
      await ffmpegClip(mainUrl, out, 4);
      console.log(`[preview] ✓ ${roomId} aus Hauptstream`);
      return;
    }
    
    throw new Error('Kein Stream verfügbar');
  } catch (e) {
    console.warn(`[preview] ✗ ${roomId}: ${e.message}`);
  }
}

function cleanupOldThumbnails(activeRoomIds) {
  try {
    const files = fs.readdirSync(THUMBS_DIR);
    files.forEach(file => {
      if (!file.endsWith('.mp4')) return;
      const roomId = file.replace('.mp4', '');
      if (!activeRoomIds.includes(roomId)) {
        const filePath = path.join(THUMBS_DIR, file);
        fs.unlinkSync(filePath);
        console.log(`[preview] 🗑️ Gelöscht: ${roomId} (Stream offline)`);
      }
    });
  } catch (e) {
    console.warn('[preview] Cleanup fehlgeschlagen:', e.message);
  }
}

async function tick() {
  // Demo-Preview sicherstellen
  await ensureDemoPreview();
  
  try {
    // Hole aktive Räume
    const data = await httpGetJson(SIGNALING_ROOMS_URL);
    const rooms = Array.isArray(data.rooms) ? data.rooms : [];
    const activeRoomIds = rooms.map(r => r.roomId).filter(Boolean);
    
    if (activeRoomIds.length === 0) {
      console.log('[preview] Keine aktiven Streams');
      return;
    }
    
    console.log(`[preview] ${activeRoomIds.length} aktive Stream(s): ${activeRoomIds.join(', ')}`);
    
    // Generiere/Aktualisiere Thumbnails für alle aktiven Räume
    for (const roomId of activeRoomIds) {
      await generateRoomPreview(roomId);
    }
    
    // Lösche Thumbnails für offline Streams
    cleanupOldThumbnails(activeRoomIds);
    
  } catch (e) {
    console.warn('[preview] Konnte Rooms nicht abrufen:', e.message);
  }
}

async function loop() {
  console.log('[preview] ═══════════════════════════════════════');
  console.log('[preview] Vollautomatischer Preview-Generator');
  console.log('[preview] Update-Intervall: 30 Sekunden');
  console.log('[preview] ═══════════════════════════════════════');
  
  while (true) {
    await tick();
    await new Promise(resolve => setTimeout(resolve, UPDATE_INTERVAL));
  }
}

loop().catch(err => {
  console.error('[preview] FATAL:', err);
  process.exit(1);
});
