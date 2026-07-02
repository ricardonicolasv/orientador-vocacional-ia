#!/bin/sh

set -e

echo "Limpiando cache de Laravel..."
php artisan optimize:clear || true

echo "Ejecutando migraciones..."
php artisan migrate --force

echo "Creando usuario orientador si no existe..."
php artisan db:seed --class=ProductionUserSeeder --force

echo "Cacheando configuracion..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Iniciando Apache..."
apache2-foreground