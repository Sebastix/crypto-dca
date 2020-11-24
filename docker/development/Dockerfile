FROM php:7.4-cli-alpine

RUN apk --no-cache update \
    && apk --no-cache add gmp-dev python3 py3-pip \
    && docker-php-ext-install -j$(nproc) gmp bcmath

# Install composer
RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer

COPY . /app/

WORKDIR /app/resources/xpub_derive

RUN pip3 install --no-cache -r requirements.txt

WORKDIR /app/
