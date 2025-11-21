FROM php:8.2-apache

# Activar mod_rewrite
RUN a2enmod rewrite

# Configurar Apache correctamente para usar /public como DocumentRoot
RUN sed -i 's#/var/www/html#/var/www/html/public#g' /etc/apache2/sites-available/000-default.conf
RUN sed -i 's#<Directory /var/www/html/>#<Directory /var/www/html/public/>#g' /etc/apache2/apache2.conf

# Configuración PHP
RUN echo "display_errors = On" >> /usr/local/etc/php/php.ini \
 && echo "error_reporting = E_ALL" >> /usr/local/etc/php/php.ini

# Copiar el proyecto
COPY . /var/www/html/

# Permisos
RUN chown -R www-data:www-data /var/www/html

# Extensiones PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

EXPOSE 80

