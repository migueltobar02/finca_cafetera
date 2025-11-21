FROM php:8.2-apache

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Cambiar DocumentRoot a /var/www/html/public
RUN sed -i 's#/var/www/html#/var/www/html/public#g' /etc/apache2/sites-available/000-default.conf

# Configurar permisos y AllowOverride
RUN printf "<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n" >> /etc/apache2/apache2.conf

# Evitar warnings de ServerName
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Instalar extensiones PHP necesarias
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar todos los archivos
COPY . /var/www/html/

# Dar permisos correctos
RUN chown -R www-data:www-data /var/www/html
