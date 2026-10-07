FROM php:8.2-cli

RUN useradd -m secure_programming
WORKDIR /home/secure_programming

COPY . app/
WORKDIR app/

RUN chown -R secure_programming:secure_programming /home/secure_programming/app

USER secure_programming

ENV LC_ALL C.UTF-8
ENV LANG C.UTF-8
EXPOSE 8081
ENTRYPOINT ["php", "index.php"]
