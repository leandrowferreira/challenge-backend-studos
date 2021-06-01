#!/bin/sh

docker-compose exec appslug bash -c 'php artisan migrate:fresh --seed'