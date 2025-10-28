# Gunakan PHP 8.3 (biar sama dengan lokal)
FROM php:8.3-fpm

# Fix DNS (ini penting di Docker Desktop Linux)
# RUN echo "nameserver 8.8.8.8" > /etc/resolv.conf

# Install dependencies Laravel
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libonig-dev libxml2-dev zip curl libzip-dev \
 && docker-php-ext-install zip

# Install ekstensi PHP yang dibutuhkan Laravel
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Set working directory
WORKDIR /var/www/html

# Copy semua file project ke container
COPY . .

# Install Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Jalankan Composer install
RUN composer install

# Expose port 9000 untuk PHP-FPM
EXPOSE 9000

# Jalankan PHP-FPM
CMD ["php-fpm"]
