FROM php:8.2-apache

# System deps + PHP extensions for MySQL (PDO)
RUN apt-get update && apt-get install -y \
    git unzip \
    && docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy backend code into Apache web root
WORKDIR /var/www/html
COPY . /var/www/html

# Install PHP dependencies (vendor/)
RUN if [ -f composer.json ]; then composer install --no-dev --prefer-dist --no-interaction; fi

# Ensure Apache serves from /var/www/html (default)
EXPOSE 80
