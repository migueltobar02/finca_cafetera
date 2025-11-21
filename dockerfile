# Imagen con PHP 8.2 + Apache
FROM php:8.2-apache

# Instalar extensiones necesarias para MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Copiar el proyecto completo
COPY . /var/www/html/

# Establecer la carpeta public como raíz del servidor
WORKDIR /var/www/html/public/

# Permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html
