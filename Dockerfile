FROM php:8.2-apache

# Activar mod_rewrite si usas .htaccess
RUN a2enmod rewrite

# Copiar TODO el proyecto, no solo public/
COPY . /var/www/html/

# Permisos correctos
RUN chown -R www-data:www-data /var/www/html

# Instalar extensiones PHP necesarias
RUN docker-php-ext-install mysqli pdo pdo_mysql

EXPOSE 80