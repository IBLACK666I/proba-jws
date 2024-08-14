FROM php:8.3.10-cli

RUN apt-get update -y && apt-get install -y libmcrypt-dev git libssl-dev

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN pecl install mongodb
RUN docker-php-ext-enable mongodb
WORKDIR /app
COPY . /app
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install
RUN COMPOSER_ALLOW_SUPERUSER=1 composer require mongodb/mongodb symfony/debug-bundle --dev

EXPOSE 8000
ENTRYPOINT ["php", "bin/console", "serve", "-d"]