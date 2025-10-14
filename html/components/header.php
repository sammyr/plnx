<link rel="stylesheet" href="components/header.css">
<header class="topnav">
  <nav class="topnav-inner">
    <a class="brand" href="viewer.php">
      <?php include __DIR__ . '/logo.php'; ?>
    </a>
    <div class="nav-right" style="text-transform: uppercase;">
      <a class="navlink" href="viewer.php">Home</a>
      <a class="navlink" href="diagnose.php">Diagnose</a>
      <a class="navlink" href="login.php">Mitarbeiter-Login</a>
    
    </div>
  </nav>
</header>
<script>
document.getElementById('start-hls-btn')?.addEventListener('click', async () => {
  const room = prompt('Room-ID (z.B. demo_video_stream):', 'demo_video_stream');
  if (!room) return;
  const file = prompt('Datei aus /videos (z.B. Subway.m4v):', 'Subway.m4v') || 'Subway.m4v';
  try {
    const url = `https://ws.sammyrichter.de/api/hls/start?room=${encodeURIComponent(room)}&file=${encodeURIComponent(file)}`;
    const r = await fetch(url, { method: 'POST' });
    const j = await r.json().catch(() => ({}));
    alert('HLS gestartet: ' + (j.roomId || room));
  } catch (e) {
    alert('Fehler beim Start: ' + (e && e.message ? e.message : e));
  }
});
</script>
