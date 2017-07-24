#!/usr/bin/env bash

chmod +rw vendor/composer/installed.json
docker-compose down
docker-compose up -d selenium
set -e

docker-compose up -d $1

echo "Creating the $1 shop this will take 2 minutes"
sleep 120
docker logs prestashop_prestashop17_1
echo "adjust the time in order to see the apache start logs"

echo "Basic $1 testing" && composer install
vendor/bin/phpunit --group $1 --group basic
chmod +rw vendor/composer/installed.json

echo "Install $1 testing" && composer install
vendor/bin/phpunit --group $1 --group install
chmod +rw vendor/composer/installed.json

echo "Register $1 testing" && composer install
vendor/bin/phpunit --group $1 --group register
chmod +rw vendor/composer/installed.json

echo "Buying $1 testing" && composer install
vendor/bin/phpunit --group $1 --group buy
chmod +rw vendor/composer/installed.json
