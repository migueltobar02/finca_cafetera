FROM php:8.2-apache

# Activar mod_rewrite
RUN a2enmod rewrite

# Mostrar errores PHP para debug
RUN echo "display_errors = On" >> /usr/local/etc/php/php.ini \
 && echo "error_reporting = E_ALL" >> /usr/local/etc/php/php.ini

# Copiar todo el proyecto
COPY . /var/www/html/

# Cambiar DocumentRoot a public/
RUN sed -i 's#/var/www/html#/var/www/html/public#g' /etc/apache2/sites-available/000-default.conf \
 && sed -i 's#<Directory /var/www/>#<Directory /var/www/html/public/>#g' /etc/apache2/apache2.conf \
 && sed -i 's#/var/www/#/var/www/html/public/#g' /etc/apache2/apache2.conf

# Permisos correctos
RUN chown -R www-data:www-data /var/www/html

# Instalar extensiones PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

EXPOSE 80
