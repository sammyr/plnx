#!/bin/bash
# Automatische Poster-Generierung via Cron-Job

# Füge Cron-Job hinzu (läuft alle 5 Minuten)
(crontab -l 2>/dev/null; echo "*/5 * * * * cd /var/www/html && php generate-posters.php >> /var/log/poster-generator.log 2>&1") | crontab -

echo "✅ Cron-Job eingerichtet!"
echo "📋 Poster werden automatisch alle 5 Minuten generiert"
echo "📝 Logs: /var/log/poster-generator.log"
echo ""
echo "Zum Anzeigen der Cron-Jobs:"
echo "  crontab -l"
echo ""
echo "Zum Entfernen:"
echo "  crontab -r"
