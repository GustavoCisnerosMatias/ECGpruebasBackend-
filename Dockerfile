# Imagen base de PHP con Apache
FROM php:8.2-apache

# Habilitar extensiones necesarias
RUN docker-php-ext-install pdo pdo_mysql

# Copiar todo el contenido del proyecto al directorio web del contenedor
COPY . /var/www/html/

# Habilitar Apache mod_rewrite (importante para Fat-Free Framework)
RUN a2enmod rewrite

# Establecer permisos correctos
RUN chown -R www-data:www-data /var/www/html

# Configurar Apache para usar index.php como entrada
RUN echo '<Directory /var/www/html/>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n\
\n\
<VirtualHost *:80>\n\
    DocumentRoot /var/www/html\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Puerto que usará Apache
EXPOSE 80
