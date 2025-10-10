<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Location: stream.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <?php include 'components/head-meta.php'; ?>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="components/global.css">
    <style>
        :root {
            --bg: #0a0a0f;
            --card: #1a1a24;
            --text: #ffffff;
            --muted: #a1a1aa;
            --border: rgba(255,255,255,0.1);
            --accent: #d4af37;
        }
        * { box-sizing: border-box; }
        .card {
            width: 100%; max-width: 520px; background: var(--card); border: 1px solid var(--border);
            border-radius: 20px; padding: 40px; box-shadow: 0 30px 60px rgba(0,0,0,0.4);
        }
        h1 { margin: 0 0 8px; font-weight: 600; font-size: 22px; letter-spacing: .2px; }
        p { margin: 0 0 20px; color: var(--muted); font-size: 14px; }
        label { display: block; font-size: 13px; color: var(--muted); margin-bottom: 8px; }
        input {
            width: 100%; padding: 12px 14px; border-radius: 10px; border: 1px solid var(--border);
            background: #121219; color: var(--text); outline: none; font-family: inherit; font-size: 14px;
        }
        .field { margin-bottom: 14px; }
        .btn {
            width: 100%; padding: 12px 16px; border-radius: 10px; border: 1px solid rgba(212,175,55,0.4);
            background: linear-gradient(135deg, rgba(212,175,55,0.18), rgba(244,208,63,0.12));
            color: var(--accent); font-weight: 700; letter-spacing: .6px; cursor: pointer; transition: .15s;
        }
        .btn:hover { filter: brightness(1.08); }
        .links { margin-top: 14px; display: flex; justify-content: space-between; font-size: 12px; }
        .links a { color: var(--muted); text-decoration: none; }
        .links a:hover { color: var(--text); }
    </style>
</head>
<body>
    <?php include 'components/header.php'; ?>
    <div class="page-wrapper">
        <div class="content-wrapper">
            <form class="card" method="post">
        <h1>Mitarbeiter-Login</h1>
        <p>Bitte melde dich an, um zu Broadcasten.</p>
        <div class="field">
            <label for="username">Benutzername</label>
            <input id="username" name="username" autocomplete="username" required>
        </div>
        <div class="field">
            <label for="password">Passwort</label>
            <input id="password" type="password" name="password" autocomplete="current-password" required>
        </div>
        <button class="btn" type="submit">Anmelden</button>
        <div class="links">
            <a href="viewer.php">Zurück</a>
            <a href="#">Passwort vergessen?</a>
        </div>
            </form>
        </div>
    </div>
    <?php include 'components/footer.php'; ?>
</body>
</html>
