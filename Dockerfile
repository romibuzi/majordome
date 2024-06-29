FROM php:8.2-cli

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update

RUN apt-get -y install libzip-dev sqlite3

RUN docker-php-ext-install zip

RUN curl -sS https://getcomposer.org/installer -o composer-setup.php

RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer -y

WORKDIR /opt/majordome

COPY . .

RUN make install-dev

RUN make install-db

CMD ["php", "-S", "0.0.0.0:8080", "-t", "public/"]

EXPOSE 8080
