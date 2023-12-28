# Usa la imagen base de PHP 8.2
FROM php:8.2-fpm

# Instala las dependencias necesarias, incluyendo el cliente de PostgreSQL y las herramientas necesarias para compilar extensiones PHP
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql

# Establece el directorio de trabajo
WORKDIR /var/www/html

# Copia el resto de tu aplicación
COPY . .

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

CMD php artisan serve --host=0.0.0.0 --port=8000
