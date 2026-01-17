#!/bin/bash

set -e
# Run migrations
php artisan migrate --force

php artisan db:seed --force

