#!/bin/bash

# Hetzner Ubuntu Server Setup Script
# Automatische Installation und Konfiguration

set -e

echo "=========================================="
echo "  Stream Server Setup - Hetzner Ubuntu"
echo "=========================================="
echo ""

# Farben für Output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Funktion für farbige Ausgabe
print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_info() {
    echo -e "${YELLOW}→${NC} $1"
}

# 1. System aktualisieren
print_info "Aktualisiere System..."
apt-get update -qq
apt-get upgrade -y -qq
print_success "System aktualisiert"

# 2. Docker installieren
print_info "Installiere Docker..."
if ! command -v docker &> /dev/null; then
    curl -fsSL https://get.docker.com -o get-docker.sh
    sh get-docker.sh
    rm get-docker.sh
    systemctl enable docker
    systemctl start docker
    print_success "Docker installiert"
else
    print_success "Docker bereits installiert"
fi

# 3. Docker Compose installieren
print_info "Installiere Docker Compose..."
if ! command -v docker-compose &> /dev/null; then
    curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
    chmod +x /usr/local/bin/docker-compose
    print_success "Docker Compose installiert"
else
    print_success "Docker Compose bereits installiert"
fi

# 4. Firewall konfigurieren
print_info "Konfiguriere Firewall..."
ufw --force enable
ufw allow 22/tcp    # SSH
ufw allow 80/tcp    # HTTP
ufw allow 443/tcp   # HTTPS
ufw allow 1935/tcp  # RTMP
ufw allow 3000/tcp  # WebRTC Signaling
ufw reload
print_success "Firewall konfiguriert"

# 5. Verzeichnisse erstellen
print_info "Erstelle Verzeichnisse..."
mkdir -p /opt/stream
mkdir -p /opt/stream/html
mkdir -p /opt/stream/deploy
mkdir -p /opt/stream/signaling-server
mkdir -p /opt/stream/logs
print_success "Verzeichnisse erstellt"

# 6. Berechtigungen setzen
print_info "Setze Berechtigungen..."
chown -R www-data:www-data /opt/stream/html
chmod -R 755 /opt/stream
print_success "Berechtigungen gesetzt"

# 7. Docker-Container starten
print_info "Starte Docker-Container..."
cd /opt/stream/deploy
docker-compose -f docker-compose.production.yml up -d
print_success "Container gestartet"

# 8. Status anzeigen
echo ""
echo "=========================================="
echo "  Installation abgeschlossen!"
echo "=========================================="
echo ""
echo "Container-Status:"
docker-compose -f docker-compose.production.yml ps
echo ""
echo "Server-URLs:"
echo "  → HTTP:              http://$(curl -s ifconfig.me)"
echo "  → RTMP:              rtmp://$(curl -s ifconfig.me):1935/live"
echo "  → WebRTC Signaling:  ws://$(curl -s ifconfig.me):3000"
echo ""
echo "Nächste Schritte:"
echo "  1. Dateien hochladen: scp -r html/* root@SERVER:/opt/stream/html/"
echo "  2. SSL einrichten:    ./setup-ssl.sh your-domain.com"
echo "  3. Logs anzeigen:     docker-compose logs -f"
echo ""
