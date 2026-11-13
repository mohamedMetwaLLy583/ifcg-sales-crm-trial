FROM php:8.2-apache-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libpq-dev \
        libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" bcmath exif gd intl pdo_mysql pdo_pgsql zip opcache \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf

RUN sed -i 's/^Listen 80$/Listen 10000/' /etc/apache2/ports.conf \
    && a2enmod rewrite headers

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-progress \
    && mkdir -p storage/app/private storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache public/uploads \
    && chown -R www-data:www-data storage bootstrap/cache public/uploads \
    && chmod -R ug+rwX storage bootstrap/cache public/uploads

COPY docker/ifcg-entrypoint.sh /usr/local/bin/ifcg-entrypoint.sh

ENV PORT=10000
EXPOSE 10000

ENTRYPOINT ["sh", "/usr/local/bin/ifcg-entrypoint.sh"]
CMD ["apache2-foreground"]
