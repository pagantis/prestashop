#!/usr/bin/env bash

docker-compose down
docker-compose up -d $1
docker-compose up -d selenium

echo "Creating the $1 shop this will take 1 minutes"
sleep 100
docker logs prestashop_prestashop17_1

echo "Basic $1 testing" && composer install && vendor/bin/phpunit --group $1 --group basic
echo "Install $1 testing" && composer install && vendor/bin/phpunit --group $1 --group install
echo "Register $1 testing" && composer install && vendor/bin/phpunit --group $1 --group register
echo "Buying $1 testing" && composer install && vendor/bin/phpunit --group $1 --group buy
