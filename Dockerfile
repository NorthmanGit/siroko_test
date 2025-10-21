FROM php:8.2-fpm-bullseye

# Instal·lar dependències i extensions per Symfony, PostgreSQL i Redis
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        git unzip zip curl libpq-dev libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

# Copiar Composer des de la imatge oficial de Composer (multi-stage build)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Instalar Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony
    
# Definir directori de treball (on estarà el codi Symfony)
WORKDIR /var/www/html

