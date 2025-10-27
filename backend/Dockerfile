# Usa la imagen base de PHP con Apache
FROM php:8.1-apache

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev

# Configuración de Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    a2enmod rewrite && \
    sed -i 's|/var/www/html|/var/www/public|' /etc/apache2/sites-available/000-default.conf

# Extensiones de PHP
RUN docker-php-ext-install pdo pdo_mysql zip

# Instalar Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Establecer directorio de trabajo
WORKDIR /var/www

# Copiar TODO el código FUENTE (incluyendo artisan)
COPY . .

# Instalar dependencias de Composer
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Instalar Node.js y npm
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs

# Verificar instalación de Node
RUN node --version && npm --version

# Instalar dependencias de Node
RUN npm ci

# Verificar que los archivos de entrada existen
RUN ls -la resources/js/app.js resources/sass/app.scss

# Compilar assets con debug
RUN npm run build

# Verificar que el build fue exitoso
RUN ls -la public/build/ && ls -la public/build/manifest.json

# Crear directorios de storage necesarios
RUN mkdir -p storage/logs \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/app/public

# Permisos en directorios críticos
RUN chmod -R 775 storage bootstrap/cache && \
    chown -R www-data:www-data /var/www

# Puerto expuesto
EXPOSE 80

# Script de inicio que ejecuta migraciones y luego Apache
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Iniciar con script personalizado
CMD ["docker-entrypoint.sh"]