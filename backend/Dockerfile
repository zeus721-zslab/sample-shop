FROM dunglas/frankenphp:latest AS base
WORKDIR /app

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libzip-dev \
    zip \
    unzip \
    libicu-dev \
    libonig-dev \
    && rm -rf /var/lib/apt/lists/*

RUN install-php-extensions \
    pdo_mysql \
    redis \
    zip \
    intl \
    bcmath \
    opcache \
    pcntl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

FROM base AS build
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist
COPY . .
RUN composer dump-autoload --optimize --no-dev --no-scripts

FROM base AS runner
COPY --from=build /app /app
RUN rm -f bootstrap/cache/packages.php bootstrap/cache/services.php \
    && php artisan package:discover --ansi \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

EXPOSE 8080
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
