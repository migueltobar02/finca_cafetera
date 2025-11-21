FROM php:8.2-apache

RUN a2enmod rewrite

# Cambiar DocumentRoot a /public
RUN sed -i 's#/var/www/html#/var/www/html/public#g' /etc/apache2/sites-available/000-default.conf

# Agregar bloque Directory
RUN printf "<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n" >> /etc/apache2/apache2.conf

# Evitar error de ServerName
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

RUN docker-php-ext-install mysqli pdo pdo_mysql
