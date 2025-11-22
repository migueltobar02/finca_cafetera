# Imagen base PHP con Apache
FROM php:8.2-apache

# Instalar extensiones necesarias (PDO + MySQL)
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip unzip git curl \
    && docker-php-ext-install pdo pdo_mysql mysqli

# Copiamos la carpeta 'public' al directorio de Apache
COPY public/ /var/www/html/

# Copiamos la carpeta 'app' para que los includes funcionen
COPY app/ /var/www/html/app/

# Copiamos la carpeta 'database' si es necesaria (opcional)
COPY database/ /var/www/html/database/

# Ajustamos permisos
RUN chown -R www-data:www-data /var/www/html/

# Habilitamos mod_rewrite si lo necesitas
RUN a2enmod rewrite

# Exponemos el puerto 80
EXPOSE 80

# Comando por defecto para iniciar Apache
CMD ["apache2-foreground"]
