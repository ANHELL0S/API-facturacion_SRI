FROM php:8.2-fpm

# Instalar todas las dependencias en una sola capa
RUN apt-get update && apt-get install -y \
    # Certificados y utilidades básicas
    ca-certificates \
    gnupg \
    curl \
    unzip \
    git \
    # OpenJDK
    default-jdk \
    # Librerías para extensiones PHP
    libpq-dev \
    libzip-dev \
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
    # Cliente MySQL
    default-mysql-client \
    # Node.js y npm
    nodejs \
    npm \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Verificar instalación de Java
RUN java -version

# Configurar extensión GD
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Instalar extensiones PHP en una sola ejecución
RUN docker-php-ext-install -j$(nproc) \
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
    fileinfo

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Establecer directorio de trabajo
WORKDIR /var/www

# Configurar PHP-FPM
RUN sed -i 's/listen = 127.0.0.1:9000/listen = 0.0.0.0:9000/' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/^pm.max_children = .*/pm.max_children = 50/' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/^pm.start_servers = .*/pm.start_servers = 5/' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/^pm.min_spare_servers = .*/pm.min_spare_servers = 5/' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/^pm.max_spare_servers = .*/pm.max_spare_servers = 35/' /usr/local/etc/php-fpm.d/www.conf && \
    echo "request_terminate_timeout = 300" >> /usr/local/etc/php-fpm.d/www.conf

# Crear configuración PHP personalizada
RUN echo "upload_max_filesize = 100M" > /usr/local/etc/php/conf.d/laravel.ini && \
    echo "post_max_size = 100M" >> /usr/local/etc/php/conf.d/laravel.ini && \
    echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/laravel.ini && \
    echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/laravel.ini && \
    echo "max_input_time = 300" >> /usr/local/etc/php/conf.d/laravel.ini

# Exponer puerto PHP-FPM
EXPOSE 9000

# Comando por defecto
CMD ["php-fpm"]