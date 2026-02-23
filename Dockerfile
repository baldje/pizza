# Базовый образ: PHP с FPM
FROM php:8.4-fpm

# Устанавливаем системные пакеты и расширения PHP, нужные Laravel
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Устанавливаем Composer (берем из официального образа)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Указываем рабочую папку внутри контейнера
WORKDIR /var/www

# Копируем проект внутрь контейнера
COPY . .

# Устанавливаем зависимости Laravel
RUN composer install --optimize-autoloader --no-dev

# Настраиваем права на storage и bootstrap/cache
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# php-fpm работает на порту 9000
EXPOSE 9000
CMD ["php-fpm"]
