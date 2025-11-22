# Imagen base PHP con Apache
FROM php:8.2-apache

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
