FROM php:8.2-cli

#dependencies
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libzip-dev


RUN docker-php-ext-install zip

#install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

#install bundles
COPY ./KickScore/composer.* ./
RUN composer install --no-scripts

#exec php sniffer
CMD vendor/bin/phpcs --standard=PSR12 src/ && vendor/bin/phpcbf --standard=PSR12 src/
