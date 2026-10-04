FROM php:8.2-apache

ARG APP_PORT=8081

RUN sed -i "s/Listen 80/Listen ${APP_PORT}/" /etc/apache2/ports.conf \
    && sed -i "s/:80>/:${APP_PORT}>/" /etc/apache2/sites-available/000-default.conf

EXPOSE ${APP_PORT}
