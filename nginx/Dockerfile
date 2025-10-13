FROM nginx:alpine

# Kopiere die Nginx-Konfiguration
COPY default.conf /etc/nginx/conf.d/default.conf

# Exponiere Port 80
EXPOSE 80

# Starte Nginx
CMD ["nginx", "-g", "daemon off;"]
