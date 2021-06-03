#!/bin/sh

docker-compose exec appslug bash -c 'composer install'
docker-compose exec appslug bash -c 'chmod 777 storage -R'
docker-compose exec appslug bash -c 'cp .env.example .env'
docker-compose exec appslug bash -c 'php artisan migrate:fresh --seed'
