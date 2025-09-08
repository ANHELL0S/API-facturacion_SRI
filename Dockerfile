FROM php:8.2-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    libpq-dev libzip-dev unzip git curl nodejs npm \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip bcmath

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copiar proyecto
COPY . .

# Instalar dependencias PHP
RUN composer install --no-dev --optimize-autoloader

# Compilar assets con Vite
RUN npm install && npm run build

CMD ["php-fpm"]
