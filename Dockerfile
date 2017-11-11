FROM php:7-alpine
RUN docker-php-ext-install pdo_mysql
