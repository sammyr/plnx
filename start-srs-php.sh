#!/bin/bash

echo "=========================================="
echo "  Starting SRS + PHP-FPM + Nginx"
echo "=========================================="

# Starte PHP-FPM
echo "Starting PHP-FPM..."
service php7.4-fpm start

# Starte Nginx
echo "Starting Nginx..."
service nginx start

# Starte SRS
echo "Starting SRS..."
cd /usr/local/srs
./objs/srs -c conf/srs.conf

# Keep container running
tail -f /dev/null
