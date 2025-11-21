FROM php:8.2-apache

# Activar mod_rewrite si usas .htaccess
RUN a2enmod rewrite

# Copiar TODO lo que haya en public/ a Apache
COPY public/ /var/www/html/

# Permisos correctos
RUN chown -R www-data:www-data /var/www/html

# Instalar extensiones PHP necesarias (si quieres otra me dices)
RUN docker-php-ext-install mysqli pdo pdo_mysql

EXPOSE 80
