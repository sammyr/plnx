#!/bin/bash

# Deployment Script - Von Windows zu Hetzner Server
# Verwendung: ./deploy.sh SERVER_IP

set -e

if [ -z "$1" ]; then
    echo "Verwendung: ./deploy.sh SERVER_IP"
    echo "Beispiel: ./deploy.sh 123.45.67.89"
    exit 1
fi

SERVER_IP=$1
SERVER_USER="root"
REMOTE_DIR="/opt/stream"

echo "=========================================="
echo "  Deployment zu Hetzner Server"
echo "  Server: $SERVER_IP"
echo "=========================================="
echo ""

# 1. Dateien hochladen
echo "→ Lade Dateien hoch..."

# HTML-Dateien
rsync -avz --progress ../html/ $SERVER_USER@$SERVER_IP:$REMOTE_DIR/html/

# Docker-Konfiguration
rsync -avz --progress docker-compose.production.yml $SERVER_USER@$SERVER_IP:$REMOTE_DIR/deploy/
rsync -avz --progress nginx.conf $SERVER_USER@$SERVER_IP:$REMOTE_DIR/deploy/

# SRS-Konfiguration
rsync -avz --progress ../srs.conf $SERVER_USER@$SERVER_IP:$REMOTE_DIR/

# Signaling-Server
rsync -avz --progress ../signaling-server/ $SERVER_USER@$SERVER_IP:$REMOTE_DIR/signaling-server/

echo "✓ Dateien hochgeladen"

# 2. Setup-Script hochladen und ausführen
echo ""
echo "→ Führe Server-Setup aus..."
scp setup-server.sh $SERVER_USER@$SERVER_IP:/tmp/
ssh $SERVER_USER@$SERVER_IP "chmod +x /tmp/setup-server.sh && /tmp/setup-server.sh"

echo ""
echo "=========================================="
echo "  Deployment abgeschlossen!"
echo "=========================================="
echo ""
echo "Server-URL: http://$SERVER_IP"
echo ""
echo "Nächste Schritte:"
echo "  1. Teste die Seite: http://$SERVER_IP"
echo "  2. SSL einrichten:  ssh root@$SERVER_IP './setup-ssl.sh your-domain.com'"
echo "  3. Logs anzeigen:   ssh root@$SERVER_IP 'cd /opt/stream/deploy && docker-compose logs -f'"
echo ""
