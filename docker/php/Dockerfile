FROM php:8.4-cli AS base

RUN apt-get update && apt-get install -y \
    git curl wget zip unzip libzip-dev libpng-dev libjpeg-dev \
    libfreetype-dev libonig-dev libxml2-dev locales nano && \
    rm -rf /var/lib/apt/lists/*

RUN locale-gen en_US.UTF-8
ENV LANG=en_US.UTF-8
ENV LANGUAGE=en_US:en
ENV LC_ALL=en_US.UTF-8

RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) pdo pdo_mysql mysqli zip gd opcache bcmath

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

FROM base AS fpm
CMD ["php-fpm"]

FROM fpm AS final
RUN useradd -u 1000 -m -s /bin/bash scheduling && \
    mkdir -p /var/www && chown -R scheduling:scheduling /var/www

COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www
USER scheduling
EXPOSE 9000
CMD ["php-fpm"]