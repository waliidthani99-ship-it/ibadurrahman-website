FROM php:8.3-cli

# pdo_mysql keeps the existing admin/registration backend working.
RUN apt-get update \
    && apt-get install -y --no-install-recommends default-libmysqlclient-dev \
    && docker-php-ext-install pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY . /app

EXPOSE 10000
CMD php -S 0.0.0.0:${PORT:-10000} -t /app /app/router.php
