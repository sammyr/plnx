# 🚀 Deployment auf Hetzner Ubuntu Cloud Server

## 📋 Voraussetzungen

### Auf deinem Windows-PC:
- ✅ SSH-Client (OpenSSH oder PuTTY)
- ✅ SCP für Dateiübertragung
- ✅ Hetzner Cloud Server (Ubuntu 22.04 LTS)

### Auf dem Hetzner Server:
- ✅ Ubuntu 22.04 LTS
- ✅ Root-Zugriff
- ✅ Öffentliche IP-Adresse

---

## 🎯 Schnell-Deployment (Einfachste Methode)

### Schritt 1: Server vorbereiten

**SSH zum Server verbinden:**
```powershell
ssh root@DEINE_SERVER_IP
```

### Schritt 2: Deployment ausführen

**Von deinem Windows-PC:**
```powershell
cd d:\___SYSTEM\Desktop\_PYTHON\stream\deploy
.\deploy.ps1 -ServerIP "DEINE_SERVER_IP"
```

**Fertig!** 🎉

---

## 📝 Detaillierte Anleitung

### 1. Hetzner Cloud Server erstellen

1. Gehe zu https://console.hetzner.cloud/
2. Erstelle neuen Server:
   - **Image:** Ubuntu 22.04
   - **Type:** CX11 (oder größer)
   - **Location:** Nürnberg (oder näher)
   - **SSH Key:** Deinen Public Key hinzufügen

3. Notiere die **IP-Adresse**

### 2. Erste Verbindung zum Server

```powershell
ssh root@DEINE_SERVER_IP
```

### 3. Deployment-Methoden

#### **Option A: Automatisches Deployment (Empfohlen)**

**Windows PowerShell:**
```powershell
cd d:\___SYSTEM\Desktop\_PYTHON\stream\deploy
.\deploy.ps1 -ServerIP "123.45.67.89"
```

**Linux/Mac/WSL:**
```bash
cd /mnt/d/___SYSTEM/Desktop/_PYTHON/stream/deploy
chmod +x deploy.sh
./deploy.sh 123.45.67.89
```

#### **Option B: Manuelles Deployment**

**1. Dateien hochladen:**
```powershell
# HTML-Dateien
scp -r ..\html\* root@SERVER_IP:/opt/stream/html/

# Docker-Konfiguration
scp docker-compose.production.yml root@SERVER_IP:/opt/stream/deploy/
scp nginx.conf root@SERVER_IP:/opt/stream/deploy/

# SRS-Konfiguration
scp ..\srs.conf root@SERVER_IP:/opt/stream/

# Signaling-Server
scp -r ..\signaling-server\* root@SERVER_IP:/opt/stream/signaling-server/
```

**2. Setup-Script hochladen:**
```powershell
scp setup-server.sh root@SERVER_IP:/tmp/
```

**3. Setup ausführen:**
```bash
ssh root@SERVER_IP
chmod +x /tmp/setup-server.sh
/tmp/setup-server.sh
```

---

## 🔧 Nach dem Deployment

### Server testen

**Öffne im Browser:**
```
http://DEINE_SERVER_IP
```

Du solltest die Viewer-Seite sehen! ✅

### Container-Status prüfen

```bash
ssh root@SERVER_IP
cd /opt/stream/deploy
docker-compose -f docker-compose.production.yml ps
```

### Logs anzeigen

```bash
# Alle Container
docker-compose -f docker-compose.production.yml logs -f

# Nur Nginx
docker-compose -f docker-compose.production.yml logs -f nginx

# Nur WebRTC
docker-compose -f docker-compose.production.yml logs -f webrtc-signaling
```

---

## 🔒 SSL/HTTPS einrichten (Optional)

### Mit Let's Encrypt (Kostenlos)

**1. Domain auf Server-IP zeigen lassen:**
- A-Record: `stream.deine-domain.de` → `SERVER_IP`

**2. SSL-Zertifikat erstellen:**
```bash
ssh root@SERVER_IP

# Certbot installieren
apt-get install -y certbot python3-certbot-nginx

# Zertifikat erstellen
certbot --nginx -d stream.deine-domain.de

# Automatische Erneuerung
certbot renew --dry-run
```

**3. Nginx-Konfiguration anpassen:**
- Uncomment HTTPS-Block in `nginx.conf`
- Container neu starten

---

## 📊 Server-Verwaltung

### Container starten/stoppen

```bash
cd /opt/stream/deploy

# Starten
docker-compose -f docker-compose.production.yml up -d

# Stoppen
docker-compose -f docker-compose.production.yml stop

# Neu starten
docker-compose -f docker-compose.production.yml restart

# Komplett entfernen
docker-compose -f docker-compose.production.yml down
```

### Dateien aktualisieren

**Von Windows:**
```powershell
# Nur HTML-Dateien
scp -r ..\html\* root@SERVER_IP:/opt/stream/html/

# Container neu starten
ssh root@SERVER_IP "cd /opt/stream/deploy && docker-compose restart nginx"
```

### Logs überwachen

```bash
# Live-Logs
docker-compose -f docker-compose.production.yml logs -f

# Letzte 100 Zeilen
docker-compose -f docker-compose.production.yml logs --tail=100

# Nur Fehler
docker-compose -f docker-compose.production.yml logs | grep -i error
```

---

## 🌐 URLs nach Deployment

### Öffentliche URLs:

```
Viewer-Seite:       http://DEINE_SERVER_IP/
Premium Watch:      http://DEINE_SERVER_IP/watch.html
Test-Video:         http://DEINE_SERVER_IP/test-video.html

RTMP Stream:        rtmp://DEINE_SERVER_IP:1935/live/livestream
WebRTC Signaling:   ws://DEINE_SERVER_IP:3000
```

### Mit Domain (nach SSL-Setup):

```
Viewer-Seite:       https://stream.deine-domain.de/
Premium Watch:      https://stream.deine-domain.de/watch.html

RTMP Stream:        rtmp://stream.deine-domain.de:1935/live/livestream
WebRTC Signaling:   wss://stream.deine-domain.de/ws
```

---

## 🔥 Firewall-Regeln

**Automatisch konfiguriert durch Setup-Script:**

```
Port 22   (SSH)
Port 80   (HTTP)
Port 443  (HTTPS)
Port 1935 (RTMP)
Port 3000 (WebRTC Signaling)
```

**Manuell hinzufügen:**
```bash
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw allow 1935/tcp
ufw allow 3000/tcp
ufw enable
```

---

## 📈 Performance-Optimierung

### Für mehr Zuschauer:

**1. Server upgraden:**
- CX11 → CX21 (mehr RAM/CPU)
- Oder größer je nach Bedarf

**2. Nginx-Caching aktivieren:**
```nginx
# In nginx.conf
proxy_cache_path /var/cache/nginx levels=1:2 keys_zone=my_cache:10m;
```

**3. CDN verwenden:**
- Cloudflare (kostenlos)
- BunnyCDN
- AWS CloudFront

---

## 🐛 Problembehandlung

### Problem: Container starten nicht

```bash
# Logs prüfen
docker-compose logs

# Container einzeln starten
docker-compose up nginx
```

### Problem: Seite nicht erreichbar

```bash
# Nginx-Status prüfen
docker-compose ps nginx

# Firewall prüfen
ufw status

# Port-Test
netstat -tulpn | grep :80
```

### Problem: Video lädt nicht

```bash
# Dateiberechtigungen prüfen
ls -la /opt/stream/html/videos/

# Berechtigungen setzen
chown -R www-data:www-data /opt/stream/html
chmod -R 755 /opt/stream/html
```

---

## 🔄 Update-Prozess

### Code-Updates deployen:

```powershell
# Von Windows
cd d:\___SYSTEM\Desktop\_PYTHON\stream\deploy
.\deploy.ps1 -ServerIP "DEINE_SERVER_IP"
```

### Container-Updates:

```bash
ssh root@SERVER_IP
cd /opt/stream/deploy

# Images aktualisieren
docker-compose pull

# Container neu starten
docker-compose up -d
```

---

## 💾 Backup

### Wichtige Dateien sichern:

```bash
# Auf dem Server
cd /opt/stream
tar -czf backup-$(date +%Y%m%d).tar.gz html/ deploy/ signaling-server/

# Backup herunterladen
scp root@SERVER_IP:/opt/stream/backup-*.tar.gz ./
```

---

## 📞 Support & Monitoring

### Server-Monitoring einrichten:

```bash
# Netdata installieren (optional)
bash <(curl -Ss https://my-netdata.io/kickstart.sh)

# Zugriff: http://SERVER_IP:19999
```

### Automatische Updates:

```bash
# Unattended Upgrades
apt-get install unattended-upgrades
dpkg-reconfigure -plow unattended-upgrades
```

---

## ✅ Checkliste

- [ ] Hetzner Server erstellt
- [ ] SSH-Zugriff funktioniert
- [ ] Deployment-Script ausgeführt
- [ ] Container laufen: `docker-compose ps`
- [ ] Seite erreichbar: `http://SERVER_IP`
- [ ] Video spielt ab
- [ ] WebRTC funktioniert
- [ ] (Optional) Domain konfiguriert
- [ ] (Optional) SSL eingerichtet
- [ ] (Optional) Backup eingerichtet

---

## 🎉 Fertig!

Dein Stream-Server läuft jetzt auf Hetzner!

**Zugriff:**
```
http://DEINE_SERVER_IP
```

**Bei Problemen:**
- Logs prüfen: `docker-compose logs -f`
- Firewall prüfen: `ufw status`
- Container-Status: `docker-compose ps`
