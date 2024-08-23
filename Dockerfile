FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
  git zip unzip libpng-dev \
  libzip-dev default-mysql-client\
  libssl-dev pkg-config

RUN docker-php-ext-install pdo pdo_mysql zip gd

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www

COPY . /var/www

# Install PHP dependencies
RUN pecl install mongodb && docker-php-ext-enable mongodb

RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-scripts --no-interaction --prefer-dist

RUN a2enmod rewrite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-scripts --no-autoloader
RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony
EXPOSE 8000

RUN sed -i 's!/var/www/html!/var/www/html/public!g' \
  /etc/apache2/sites-available/000-default.conf

CMD ["symfony", "server:start"]


