#!/bin/bash
# Wechsel von Nginx+PHP-FPM zu Apache mit mod_php
# Führe dieses Script auf dem Server aus

echo "🔄 Wechsel zu Apache mit mod_php..."
echo ""

# Farben
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 1. Stoppe alte Container
echo -e "${YELLOW}1. Stoppe Nginx und PHP-FPM...${NC}"
docker stop nginx-vsgk4wcsoggo800w4ow48gos-220528696351 2>/dev/null || true
docker stop php-fpm-vsgk4wcsoggo800w4ow48gos-220528709828 2>/dev/null || true
echo -e "${GREEN}✅ Container gestoppt${NC}"
echo ""

# 2. Erstelle Dockerfile für Apache
echo -e "${YELLOW}2. Erstelle Apache Dockerfile...${NC}"
cat > Dockerfile.apache << 'EOF'
FROM php:8.2-apache

# Installiere notwendige Extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Aktiviere Apache Module
RUN a2enmod rewrite headers expires

# Kopiere PHP Konfiguration
COPY php-custom.ini /usr/local/etc/php/conf.d/custom.ini

# Setze Berechtigungen
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Exponiere Port 80
EXPOSE 80

# Starte Apache
CMD ["apache2-foreground"]
EOF
echo -e "${GREEN}✅ Dockerfile erstellt${NC}"
echo ""

# 3. Erstelle php-custom.ini falls nicht vorhanden
echo -e "${YELLOW}3. Erstelle PHP Konfiguration...${NC}"
if [ ! -f "php-custom.ini" ]; then
    cat > php-custom.ini << 'EOF'
memory_limit = 256M
max_execution_time = 30
upload_max_filesize = 50M
post_max_size = 50M
max_input_time = 60
opcache.enable = 1
opcache.memory_consumption = 128
display_errors = Off
log_errors = On
EOF
    echo -e "${GREEN}✅ php-custom.ini erstellt${NC}"
else
    echo -e "${GREEN}✅ php-custom.ini existiert bereits${NC}"
fi
echo ""

# 4. Baue Apache Image
echo -e "${YELLOW}4. Baue Apache Docker Image...${NC}"
docker build -f Dockerfile.apache -t plnx-apache .
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Image erfolgreich gebaut${NC}"
else
    echo -e "${RED}❌ Fehler beim Bauen des Images${NC}"
    exit 1
fi
echo ""

# 5. Finde HTML Verzeichnis
echo -e "${YELLOW}5. Suche HTML Verzeichnis...${NC}"
HTML_PATH="/var/www/html"
if [ ! -d "$HTML_PATH" ]; then
    # Versuche alternatives Verzeichnis
    HTML_PATH=$(pwd)/html
fi
echo -e "${GREEN}✅ HTML Pfad: $HTML_PATH${NC}"
echo ""

# 6. Starte Apache Container
echo -e "${YELLOW}6. Starte Apache Container...${NC}"
docker run -d \
  --name plnx-apache \
  --restart always \
  -p 8080:80 \
  -v "$HTML_PATH":/var/www/html \
  --network coolify \
  plnx-apache

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Apache Container gestartet${NC}"
else
    echo -e "${RED}❌ Fehler beim Starten des Containers${NC}"
    exit 1
fi
echo ""

# 7. Warte auf Start
echo -e "${YELLOW}7. Warte auf Apache Start...${NC}"
sleep 5
echo ""

# 8. Prüfe Status
echo -e "${YELLOW}8. Prüfe Container Status...${NC}"
docker ps | grep plnx-apache
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Apache läuft${NC}"
else
    echo -e "${RED}❌ Apache läuft nicht${NC}"
    docker logs plnx-apache --tail 50
    exit 1
fi
echo ""

# 9. Teste Apache
echo -e "${YELLOW}9. Teste Apache...${NC}"
curl -I http://localhost:8080 2>/dev/null | head -5
echo ""

# 10. Zeige Logs
echo -e "${YELLOW}10. Apache Logs:${NC}"
docker logs plnx-apache --tail 20
echo ""

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "${GREEN}✨ Apache erfolgreich gestartet!${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📋 Nächste Schritte:"
echo "1. Teste die Seite: http://YOUR_SERVER_IP:8080"
echo "2. Aktualisiere Traefik/Proxy Konfiguration für Port 8080"
echo "3. Entferne alte Container (optional):"
echo "   docker rm nginx-vsgk4wcsoggo800w4ow48gos-220528696351"
echo "   docker rm php-fpm-vsgk4wcsoggo800w4ow48gos-220528709828"
echo ""
echo "🔍 Nützliche Befehle:"
echo "   docker logs plnx-apache -f          # Live Logs"
echo "   docker restart plnx-apache          # Neustart"
echo "   docker exec -it plnx-apache bash    # Shell"
echo ""
