FROM forceedge01/php71cli-composer:latest

WORKDIR '/app'
COPY composer.json .
COPY composer.lock .
RUN composer install
COPY . .

CMD ["./vendor/bin/phpunit", "-c", "tests"]
