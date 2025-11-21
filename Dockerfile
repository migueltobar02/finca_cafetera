FROM php:8.2-apache

# Activar mod_rewrite
RUN a2enmod rewrite

# Mostrar errores PHP para debug
RUN echo "display_errors = On" >> /usr/local/etc/php/php.ini
RUN echo "error_reporting = E_ALL" >> /usr/local/etc/php/php.ini

# Copiar todo el proyecto
COPY . /var/www/html/

# Permisos correctos
RUN chown -R www-data:www-data /var/www/html

# Instalar extensiones PHP necesarias
RUN docker-php-ext-install mysqli pdo pdo_mysql

EXPOSE 80