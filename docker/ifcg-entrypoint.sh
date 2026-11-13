#!/bin/sh
set -eu

if [ -n "${APP_KEY:-}" ]; then
    case "$APP_KEY" in
        base64:*) ;;
        *) export APP_KEY="base64:$APP_KEY" ;;
    esac
fi

mkdir -p storage/app/private storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache public/uploads
chown -R www-data:www-data storage bootstrap/cache public/uploads
chmod -R ug+rwX storage bootstrap/cache public/uploads

if [ ! -e public/storage ]; then
    php artisan storage:link
fi

su -s /bin/sh www-data -c 'php artisan config:clear'
su -s /bin/sh www-data -c 'php artisan migrate --force'
su -s /bin/sh www-data -c 'php artisan db:seed --class=Database\Seeders\IfcgDemoSeeder --force'

exec "$@"
