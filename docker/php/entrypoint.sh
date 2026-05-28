#!/bin/bash
set -e
cd /var/www/html

# Permisos storage y bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Instalar dependencias Composer si no existen
if [ ! -d vendor ]; then
    echo "[setup] composer install..."
    su -s /bin/bash www-data -c "composer install --no-interaction --prefer-dist --optimize-autoloader"
fi

# Crear .env desde .env.example si no existe
if [ ! -f .env ]; then
    echo "[setup] copiando .env.example -> .env"
    cp .env.example .env
    chown www-data:www-data .env
fi

# Generar APP_KEY si está vacía
if grep -q "^APP_KEY=$" .env; then
    echo "[setup] generando app key..."
    su -s /bin/bash www-data -c "php artisan key:generate --force"
fi

# Crear symlink storage si no existe
if [ ! -L public/storage ]; then
    echo "[setup] storage:link..."
    su -s /bin/bash www-data -c "php artisan storage:link"
fi

# Correr migraciones
echo "[setup] migraciones..."
su -s /bin/bash www-data -c "php artisan migrate --force"

exec docker-php-entrypoint "$@"
