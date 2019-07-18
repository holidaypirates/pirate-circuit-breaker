FROM php:7.3-fpm
RUN pecl install xdebug-2.7.1 && pecl install redis-4.0.1
RUN docker-php-ext-enable xdebug redis

WORKDIR "/code"
