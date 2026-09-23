FROM php:8.2-apache
RUN docker-php-ext-install mysqli

RUN sed -i 's/Listen 80/Listen 8081/' /etc/apache2/ports.conf \
    && sed -i 's/:80>/:8081>/' /etc/apache2/sites-available/000-default.conf

EXPOSE 8081
