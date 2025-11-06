# 🚀 Deployment-Anleitung

## 📋 Übersicht

Dieses Projekt unterstützt zwei Deployment-Modi:

1. **Lokale Entwicklung** (Windows/Mac) - `docker-compose.yml`
2. **Produktions-Server** (Ubuntu + Traefik) - `docker-compose.production.yml`

---

## 💻 Lokale Entwicklung (Windows/Mac)

### Voraussetzungen
- Docker Desktop installiert und gestartet
- Ports 80, 1935, 1985, 3000 verfügbar

### Start
```bash
docker-compose up -d
```

### Zugriff
- **Web-App**: http://localhost/viewer.php
- **Broadcaster**: http://localhost/stream.php
- **RTMP**: rtmp://localhost:1935/live/
- **WebSocket**: ws://localhost:3000

### Stop
```bash
docker-compose down
```

---

## 🌐 Produktions-Deployment (Ubuntu Server)

### Voraussetzungen

1. **Ubuntu Server 20.04/22.04**
2. **Docker & Docker Compose** installiert
3. **Traefik** läuft bereits als Reverse Proxy
4. **DNS-Einträge** konfiguriert:
   - `stream.sammyrichter.de` → Server-IP
   - `ws.sammyrichter.de` → Server-IP

### Traefik-Netzwerk

Stelle sicher, dass Traefik ein externes Netzwerk verwendet. Falls nicht vorhanden:

```bash
docker network create traefik-public
```

Füge dann in `docker-compose.production.yml` hinzu:

```yaml
networks:
  default:
    external: true
    name: traefik-public
```

### Deployment-Schritte

1. **Projekt auf Server hochladen**
   ```bash
   scp -r stream/ user@server:/opt/
   ```

2. **Auf Server einloggen**
   ```bash
   ssh user@server
   cd /opt/stream
   ```

3. **Container starten**
   ```bash
   docker-compose -f docker-compose.production.yml up -d
   ```

4. **Status prüfen**
   ```bash
   docker-compose -f docker-compose.production.yml ps
   docker-compose -f docker-compose.production.yml logs -f
   ```

### Zugriff (Produktion)

- **Web-App**: https://stream.sammyrichter.de/viewer.php
- **Broadcaster**: https://stream.sammyrichter.de/stream.php
- **WebSocket**: wss://ws.sammyrichter.de
- **RTMP**: rtmp://stream.sammyrichter.de:1935/live/

### SSL-Zertifikate

Traefik generiert automatisch Let's Encrypt-Zertifikate für:
- `stream.sammyrichter.de`
- `ws.sammyrichter.de`

---

## 🔄 Updates

### Lokale Entwicklung
```bash
git pull
docker-compose down
docker-compose up -d --build
```

### Produktion
```bash
cd /opt/stream
git pull
docker-compose -f docker-compose.production.yml down
docker-compose -f docker-compose.production.yml up -d --build
```

---

## 🔧 Unterschiede zwischen Dev und Production

| Feature | Entwicklung | Produktion |
|---------|-------------|------------|
| **Ports** | Direkt exponiert (80, 3000) | Nur intern, via Traefik |
| **SSL** | Kein SSL | Automatisch via Let's Encrypt |
| **Domain** | localhost | stream.sammyrichter.de |
| **WebSocket** | ws://localhost:3000 | wss://ws.sammyrichter.de |
| **Restart** | Manuell | `restart: unless-stopped` |

---

## 📊 Monitoring

### Container-Status
```bash
# Entwicklung
docker-compose ps

# Produktion
docker-compose -f docker-compose.production.yml ps
```

### Logs anzeigen
```bash
# Entwicklung
docker-compose logs -f [service-name]

# Produktion
docker-compose -f docker-compose.production.yml logs -f [service-name]
```

### Ressourcen-Nutzung
```bash
docker stats
```

---

## 🛠️ Troubleshooting

### Problem: Port 80 bereits belegt (Entwicklung)

**Lösung**: Ändere in `docker-compose.yml`:
```yaml
nginx:
  ports:
    - "8080:80"   # Statt 80:80
```

Dann Zugriff via http://localhost:8080

### Problem: Traefik findet Container nicht (Produktion)

**Prüfe Netzwerk**:
```bash
docker network inspect traefik-public
```

**Stelle sicher**, dass alle Container im gleichen Netzwerk sind.

### Problem: WebSocket-Verbindung schlägt fehl

**Entwicklung**: Prüfe ob Port 3000 offen ist
```bash
netstat -an | findstr 3000   # Windows
netstat -an | grep 3000      # Linux
```

**Produktion**: Prüfe Traefik-Routing
```bash
docker-compose -f docker-compose.production.yml logs webrtc-signaling
```

---

## 🔐 Sicherheit (Produktion)

### Empfohlene Maßnahmen

1. **Firewall konfigurieren**
   ```bash
   ufw allow 80/tcp
   ufw allow 443/tcp
   ufw allow 1935/tcp   # RTMP
   ufw enable
   ```

2. **Regelmäßige Updates**
   ```bash
   docker-compose -f docker-compose.production.yml pull
   docker-compose -f docker-compose.production.yml up -d
   ```

3. **Backup-Strategie**
   - Datenbank-Backups (falls vorhanden)
   - Video-Dateien sichern
   - Konfigurationsdateien versionieren

---

## 📝 Checkliste für Deployment

### Vor dem Deployment

- [ ] DNS-Einträge konfiguriert
- [ ] Traefik läuft auf dem Server
- [ ] Docker & Docker Compose installiert
- [ ] Ports 80, 443, 1935 sind offen
- [ ] Video-Dateien hochgeladen (`html/videos/`)

### Nach dem Deployment

- [ ] Container laufen: `docker-compose ps`
- [ ] SSL-Zertifikate generiert
- [ ] Web-App erreichbar
- [ ] WebSocket-Verbindung funktioniert
- [ ] RTMP-Streaming funktioniert
- [ ] Auto-HLS-Streams laufen

---

## 🆘 Support

Bei Problemen:
1. Logs prüfen: `docker-compose logs -f`
2. Container-Status: `docker-compose ps`
3. Netzwerk prüfen: `docker network ls`
4. Traefik-Dashboard: https://traefik.sammyrichter.de (falls konfiguriert)

---

**Viel Erfolg beim Deployment! 🚀**
