# Usa la imagen base de PHP 8.2 con FPM (más ligero que Apache para desarrollo)
FROM php:8.2-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones de PHP
RUN docker-php-ext-install pdo pdo_mysql zip mbstring exif pcntl bcmath gd

# Instalar Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crear usuario no-root
RUN useradd -G www-data,root -u 1000 -d /home/tidyuser tidyuser && \
    mkdir -p /home/tidyuser/.composer && \
    chown -R tidyuser:tidyuser /home/tidyuser

# Establecer directorio de trabajo
WORKDIR /var/www

# Copiar código
COPY --chown=tidyuser:tidyuser . /var/www

# Crear directorios de storage necesarios
RUN mkdir -p storage/logs \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/app/public \
    bootstrap/cache

# Permisos en directorios críticos
RUN chmod -R 775 storage bootstrap/cache && \
    chown -R tidyuser:tidyuser /var/www

# Cambiar al usuario no-root
USER tidyuser

# Puerto expuesto (para php artisan serve)
EXPOSE 8000