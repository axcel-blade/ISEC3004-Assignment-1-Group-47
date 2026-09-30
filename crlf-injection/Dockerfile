FROM php:8.2-cli
RUN docker-php-ext-install mysqli

ARG APP_DIR=vulnerable-app

WORKDIR /app
COPY ${APP_DIR}/ .

EXPOSE 8080

CMD ["php", "index.php"]
