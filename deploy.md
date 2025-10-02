# Sistema de Facturación Laravel - Guía de Instalación

## Requisitos Previos

-   Docker y Docker Compose instalados
-   Nginx instalado en el servidor
-   Dominio configurado (`domain`)
-   Certificado SSL (Let's Encrypt/Certbot)

## 🚀 Instalación y Configuración

### 1. Clonar el Repositorio

```bash
git clone <url-del-repositorio> facturacion
cd facturacion
```

### 2. Levantar los Contenedores Docker

```bash
# Construir e iniciar los contenedores en segundo plano
docker-compose up -d

# Verificar que los contenedores estén corriendo
docker ps
```

### 3. Configurar la Aplicación Laravel

#### Entrar al contenedor PHP:

```bash
# Usando el ID del contenedor o el nombre
docker exec -it laravel-php bash
```

#### Instalar dependencias:

```bash
# Instalar todas las dependencias de Composer
composer install

# Regenerar autoload
composer dump-autoload
```

#### Generar claves y configurar la base de datos:

```bash
# Generar clave de aplicación
php artisan key:generate

# Generar secreto JWT
php artisan jwt:secret

# Ejecutar migraciones y seeders (base de datos limpia)
php artisan migrate:fresh --seed
```

### 4. Configurar Nginx

#### Crear el archivo de configuración:

```bash
sudo nano /etc/nginx/sites-available/domain
```

#### Contenido del archivo de configuración:

```nginx
# Configuración HTTP - Redirección a HTTPS
server {
    listen 80;
    server_name domain;
    return 301 https://$server_name$request_uri;
}

# Configuración HTTPS principal
server {
    listen 443 ssl http2;
    server_name domain;

    # Configuración SSL con Certbot (Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/domain/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/domain/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;

    # Root del proyecto Laravel
    root /var/www/facturacion/public;
    index index.php index.html index.htm;

    # Logs específicos
    access_log /var/log/nginx/facturacion_access.log;
    error_log /var/log/nginx/facturacion_error.log;

    # Configuración principal para Laravel
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Procesar archivos PHP a través del contenedor Docker
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;

        # Conexión al contenedor laravel-php en puerto 9000
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;

        # Rutas dentro del contenedor
        fastcgi_param SCRIPT_FILENAME /var/www/public$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT /var/www/public;
        include fastcgi_params;

        # Configuraciones específicas
        fastcgi_param HTTP_PROXY "";
        fastcgi_param HTTPS on;
        fastcgi_param SERVER_PORT 443;

        # Performance
        fastcgi_read_timeout 300;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 256 16k;
        fastcgi_busy_buffers_size 256k;
        fastcgi_temp_file_write_size 256k;
        fastcgi_connect_timeout 60s;
        fastcgi_send_timeout 60s;
    }

    # Archivos estáticos con cache
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot|otf)$ {
        expires 1y;
        add_header Cache-Control "public, no-transform, immutable";
        add_header Vary Accept-Encoding;
        try_files $uri =404;
        gzip_static on;
    }

    # Storage público de Laravel
    location /storage/ {
        alias /var/www/facturacion/storage/app/public/;
        expires 30d;
        add_header Cache-Control "public";
        try_files $uri =404;
    }

    # Denegar acceso a archivos sensibles
    location ~ /\.(?!well-known).* {
        deny all;
        access_log off;
        log_not_found off;
    }

    # Headers de seguridad
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Configuraciones adicionales
    client_max_body_size 100M;
    client_body_timeout 60s;
    client_header_timeout 60s;
    keepalive_timeout 65s;

    # Compresión GZIP
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types
        application/atom+xml
        application/geo+json
        application/javascript
        application/x-javascript
        application/json
        application/ld+json
        application/manifest+json
        application/rdf+xml
        application/rss+xml
        application/xhtml+xml
        application/xml
        font/eot
        font/otf
        font/ttf
        image/svg+xml
        text/css
        text/javascript
        text/plain
        text/xml;
}
```

#### Habilitar el sitio y recargar Nginx:

```bash
# Crear enlace simbólico
sudo ln -s /etc/nginx/sites-available/domain /etc/nginx/sites-enabled/

# Verificar configuración
sudo nginx -t

# Recargar Nginx
sudo systemctl reload nginx
```

### 5. Configurar SSL con Certbot (si no está configurado)

```bash
# Instalar Certbot
sudo apt update
sudo apt install certbot python3-certbot-nginx

# Obtener certificado SSL
sudo certbot --nginx -d domain

# Verificar renovación automática
sudo crontab -e
# Agregar: 0 3 * * * /usr/bin/certbot renew --quiet
```

## 🔧 Comandos Útiles

### Gestión de Contenedores

```bash
# Ver contenedores activos
docker ps

# Ver logs de un contenedor
docker logs laravel-php

# Reiniciar contenedores
docker-compose restart

# Parar contenedores
docker-compose down

# Reconstruir contenedores
docker-compose up -d --build
```

### Comandos de Laravel (dentro del contenedor)

```bash
# Entrar al contenedor
docker exec -it laravel-php bash

# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rehacer migraciones y seeders
php artisan migrate:fresh --seed

# Ver rutas
php artisan route:list
```

### Logs y Debugging

```bash
# Ver logs de Nginx
sudo tail -f /var/log/nginx/facturacion_access.log
sudo tail -f /var/log/nginx/facturacion_error.log

# Ver logs de Laravel
docker exec -it laravel-php tail -f storage/logs/laravel.log
```

## 🌐 Acceso a la Aplicación

Una vez completada la instalación:

-   **URL:** https://domain
-   **Admin Panel:** https://domain/admin
-   **API Docs:** https://domain/api/documentation

## ⚠️ Troubleshooting

### Error 502 Bad Gateway

```bash
# Verificar que el contenedor PHP esté corriendo
docker ps | grep laravel-php

# Verificar conectividad al puerto 9000
telnet 127.0.0.1 9000
```

### Error de permisos

```bash
# Ajustar permisos desde el host
sudo chown -R www-data:www-data /var/www/facturacion/storage
sudo chmod -R 775 /var/www/facturacion/storage
```

### Problemas con la base de datos

```bash
# Entrar al contenedor y verificar conexión
docker exec -it laravel-php bash
php artisan tinker
DB::connection()->getPdo();
```

## 📁 Estructura del Proyecto

```
facturacion-laravel-php/
├── docker-compose.yml          # Configuración de Docker
├── Dockerfile                  # Imagen personalizada de PHP
├── nginx/                      # Configuración de Nginx
├── storage/                    # Archivos de almacenamiento
├── public/                     # Archivos públicos
└── README.md                   # Esta documentación
```

## 🔒 Seguridad

-   SSL/TLS habilitado con Let's Encrypt
-   Headers de seguridad configurados
-   Acceso restringido a archivos sensibles
-   Firewall configurado para puertos necesarios
-   Autenticación JWT implementada

---

**Soporte:** Para issues y soporte técnico, crear un ticket en el repositorio del proyecto.
