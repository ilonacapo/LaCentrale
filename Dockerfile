# Utilisation de l'image PHP-FPM
FROM php:8.2-fpm

# Installation des dépendances système
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    gnupg \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache


COPY --from=composer:2.5 /usr/bin/composer /usr/bin/composer

RUN curl -sS https://get.symfony.com/cli/installer | bash && \
    mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y nodejs && \
    npm install -g npm@latest

WORKDIR /var/www/html

COPY .env /var/www/html/.env
COPY .env.local /var/www/html/.env.local
COPY . .

RUN composer install --optimize-autoloader

RUN git config --global --add safe.directory /var/www/html
RUN php bin/console cache:clear
RUN chown -R www-data:www-data /var/www/html

EXPOSE 8000

# Commande de démarrage pour Symfony
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
