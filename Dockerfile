FROM php:8.2-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    nodejs \
    npm \
    libicu-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libxslt-dev \
    libssl-dev \
    libonig-dev \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev \
    default-mysql-client \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configurar y instalar extensiones PHP necesarias para Laravel
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    zip \
    bcmath \
    intl \
    gd \
    xsl \
    soap \
    mbstring \
    xml \
    curl \
    fileinfo \
    tokenizer \
    json \
    ctype

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Establecer directorio de trabajo
WORKDIR /var/www

# Copiar composer.json y composer.lock primero (para mejor cache de Docker)
COPY composer.json composer.lock ./

# Instalar dependencias de Composer
RUN composer install --no-dev --no-scripts --no-autoloader --optimize-autoloader

# Copiar package.json y package-lock.json para dependencias de Node
COPY package*.json ./

# Instalar dependencias de Node
RUN npm ci --only=production

# Copiar el resto del código
COPY . .

# Finalizar la instalación de Composer
RUN composer dump-autoload --optimize

# Compilar assets con Vite
RUN npm run build

# Crear directorios necesarios con permisos correctos
RUN mkdir -p storage/framework/cache \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p storage/logs \
    && mkdir -p storage/app/public \
    && mkdir -p bootstrap/cache

# Configurar permisos
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www \
    && chmod -R 775 storage \
    && chmod -R 775 bootstrap/cache

# Exponer puerto
EXPOSE 9000

# Comando por defecto
CMD ["php-fpm"]