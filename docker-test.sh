#!/usr/bin/env bash

docker-compose down
docker-compose up -d selenium
set -e

docker-compose up -d $1

echo "Creating the $1 shop this will take 1 minutes"
sleep 90
docker logs prestashop_prestashop17_1
echo "adjust the time in order to see the apache start logs"

echo "Basic $1 testing" && sudo composer install
vendor/bin/phpunit --group $1 --group basic
echo "Install $1 testing" && sudo composer install
vendor/bin/phpunit --group $1 --group install
echo "Register $1 testing" && sudo composer install
vendor/bin/phpunit --group $1 --group register
echo "Buying $1 testing" && sudo composer install
vendor/bin/phpunit --group $1 --group buy
