FROM php:8.2-apache

WORKDIR /var/www/html

# System deps
RUN apt-get update -y && apt-get upgrade -y &&     apt-get install -y git curl zip unzip libzip-dev libicu-dev gnupg &&     docker-php-ext-install intl

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Node.js 18 (LTS)
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - &&     apt-get install -y nodejs

# Copy source
COPY . .

# Apache config
COPY ./000-default.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite && service apache2 restart

# Install PHP deps
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Frontend build
RUN npm ci || npm install && npm run build

# Permissions
RUN mkdir -p /var/www/html/public/build && chown -R www-data:www-data /var/www

EXPOSE 80
