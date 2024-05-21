#!/bin/bash
set -e
echo "Deploying application"

cd /var/www/html/eatzilla-new/eatzilla-web-api/8dsny8n87t78
composer install
php artisan config:cache