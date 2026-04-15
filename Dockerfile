FROM php:8.2-apache

# Instalar dependencias del sistema necesarias
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip pdo pdo_mysql mysqli \
    && apt-get clean

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Crear carpetas necesarias
RUN mkdir -p /var/www/html/resources/views
RUN mkdir -p /var/www/html/storage/framework/cache
RUN mkdir -p /var/www/html/storage/framework/sessions
RUN mkdir -p /var/www/html/storage/framework/views
RUN mkdir -p /var/www/html/bootstrap/cache

WORKDIR /var/www/html

# Copiar todo el proyecto
COPY . .

# Instalar dependencias de Composer
RUN composer install --no-interaction --no-progress --optimize-autoloader --no-dev

# Crear vista por defecto si no existe
RUN if [ ! -f resources/views/welcome.blade.php ]; then \
    echo '<!DOCTYPE html><html><body><h1>API Funcionando</h1></body></html>' > resources/views/welcome.blade.php; \
    fi

# Dar permisos
RUN chmod -R 777 storage
RUN chmod -R 777 bootstrap/cache

# Configurar Apache
RUN chown -R www-data:www-data /var/www/html

EXPOSE 8000

# Iniciar servidor
CMD php artisan serve --host=0.0.0.0 --port=8000
