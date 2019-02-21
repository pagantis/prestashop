#!/bin/bash

if [ $2 == 'build' ]
then
    chmod -R 777 vendor
    docker-compose down
    docker-compose up -d prestashop$1
    docker-compose up -d selenium
    echo "Creating the prestashop $1"
    sleep 40
    date
    docker-compose logs prestashop$1
    set -e
fi

grunt default;

vendor/bin/phpunit --group prestashop$1basic
vendor/bin/phpunit --group prestashop$1install
vendor/bin/phpunit --group prestashop$1register
vendor/bin/phpunit --group prestashop$1buy
vendor/bin/phpunit --group prestashop$1advanced
vendor/bin/phpunit --group prestashop$1validate