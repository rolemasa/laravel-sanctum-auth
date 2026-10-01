FROM php:8.3-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
      git unzip libzip-dev libicu-dev libonig-dev \
    && docker-php-ext-install pdo_mysql zip intl bcmath mbstring \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ホストとファイルの所有者を合わせる（権限エラー防止）
ARG APP_UID=1000
ARG APP_GID=1000
RUN groupadd -o -g ${APP_GID} app \
    && useradd -o -u ${APP_UID} -g ${APP_GID} -m -s /bin/bash app

USER app
WORKDIR /var/www/html