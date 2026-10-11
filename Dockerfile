    FROM php:8.3-fpm

    # Install dependensi sistem dan ekstensi PHP
    RUN apt-get update && apt-get install -y \
        git \
        curl \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        zip \
        unzip \
        nginx

    RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

    # Install Composer 
    COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

    WORKDIR /var/www

    COPY . .

    RUN composer install --no-dev --optimize-autoloader

    # Hapus config:cache saat build, cukup bersihkan cache saja
    RUN php artisan config:clear \
        && php artisan route:clear

    # Hak akses folder storage dan cache agar bisa ditulis
    RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
        && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

    # Konfigurasi Nginx
    COPY .render/nginx.conf /etc/nginx/sites-available/default

    EXPOSE 80

    # Jalankan storage:link, bersihkan config, jalankan queue worker di background, lalu aktifkan Nginx dan PHP-FPM
    CMD php artisan storage:link && php artisan config:clear && php artisan queue:work --tries=3 & service nginx start && php-fpm