FROM php:8.2-cli
RUN docker-php-ext-install mysqli

WORKDIR /app
COPY vulnerable-app/ .

EXPOSE 8086

CMD ["php", "index.php"]
