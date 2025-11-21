# Usa PHP con Apache
FROM php:8.2-apache

# Habilitar extensiones necesarias
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Habilitar mod_rewrite (importante para frameworks y rutas limpias)
RUN a2enmod rewrite

# Configurar Apache para que el DocumentRoot sea /var/www/html/public
RUN sed -i 's#/var/www/html#/var/www/html/public#g' /etc/apache2/sites-available/000-default.conf

# Permitir .htaccess dentro de /public
RUN printf "<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n" >> /etc/apache2/apache2.conf

# Copiar tu proyecto
COPY . /var/www/html/

# Dar permisos correctos a Apache
RUN chown -R www-data:www-data /var/www/html

# Healthcheck interno para Railway
HEALTHCHECK --interval=10s --timeout=3s --retries=10 \
  CMD curl -f http://localhost/ || exit 1
