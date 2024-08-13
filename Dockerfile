# Use an official PHP 8.3 image as a base
FROM php:8.3.10-fpm
RUN apt-get update && apt-get install -y zip unzip
RUN apt-get update && apt-get install -y zip unzip
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
ENV PATH=$PATH:/usr/local/bin
RUN composer --version

# Set the working directory to /app
WORKDIR /app

# Copy the composer.json and composer.lock files to the container
COPY composer.json composer.lock /app/
RUN pecl install mongodb && docker-php-ext-enable mongodb
# Install the dependencies using Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install 

# Update the Composer autoloader
RUN composer dump-autoload --optimize

# Copy the rest of the project files to the container
COPY . /app/

# Expose the port 8000
EXPOSE 8000

# Run the command to start the Symfony server
CMD ["symfony", "server:start", "--no-debug"]