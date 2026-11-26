
# Étape 1 : compiler les ressources frontend
FROM node:24-alpine AS frontend

WORKDIR /app

COPY package*.json ./
RUN npm install

COPY . .
RUN npm run build


# Étape 2 : préparer Laravel avec PHP et Apache
FROM php:8.3-apache

# Installer les dépendances système et extensions PHP
RUN apt-get update && apt-get install -y --no-install-recommends \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        bcmath \
        exif \
        pcntl \
        intl \
        gd \
        zip \
        opcache \
    && rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* \
    && a2enmod mpm_prefork rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html

COPY . .

# Vérifier que les fichiers Laravel sont bien présents
RUN echo "=== CONTENU DU DOSSIER ===" \
    && ls -la /var/www/html \
    && echo "=== VERIFICATION COMPOSER.JSON ===" \
    && test -f /var/www/html/composer.json \
    && echo "composer.json trouvé !" \
    || (echo "ERREUR : composer.json absent !" && exit 1)

COPY --from=frontend /app/public/build ./public/build

# Configuration Apache
COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf

# Installer les dépendances PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Préparer les dossiers Laravel et les permissions
RUN mkdir -p storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/app/public \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

EXPOSE 80

CMD ["sh", "-c", "php artisan migrate --force; php artisan storage:link 2>/dev/null; exec apache2-foreground"]
