FROM php:8.3-apache

# Instalar extensiones necesarias
RUN apt-get update && apt-get install -y \
    libzip-dev unzip libpng-dev libonig-dev libxml2-dev curl \
    && docker-php-ext-install pdo_mysql zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Habilitar .htaccess y mod_rewrite
RUN a2enmod rewrite

# Copiar código fuente
COPY . /var/www/html

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Instalar dependencias de Composer
RUN composer install --ignore-platform-reqs

# Exponer el puerto por defecto de Apache
EXPOSE 80
