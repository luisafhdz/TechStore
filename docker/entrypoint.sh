#!/bin/sh
# Se ejecuta cada vez que arranca el contenedor "web".
set -e
cd /var/www/html

if [ -z "$APP_KEY" ]; then
    echo "ERROR: falta APP_KEY en .env.docker."
    echo "Genera una con:  php artisan key:generate --show"
    exit 1
fi

# El volumen de storage puede estar vacío la primera vez
mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions \
         storage/framework/views storage/logs bootstrap/cache

# Enlace public/storage -> storage/app/public (imágenes de productos)
php artisan storage:link --force >/dev/null 2>&1 || true

# Crea/actualiza las tablas (no borra datos existentes)
php artisan migrate --force

chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

exec "$@"
