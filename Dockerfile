FROM php:8.2-apache

RUN a2enmod rewrite

# Forzar Apache a servir /public como raíz
RUN sed -i 's#/var/www/html#/var/www/html/public#g' /etc/apache2/sites-available/000-default.conf

# Añadir Directory config
RUN echo "<Directory /var/www/html/public> AllowOverride All Require all granted </Directory>" >> /etc/apache2/apache2.conf

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

RUN docker-php-ext-install mysqli pdo pdo_mysql

# Arreglar ServerName
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
