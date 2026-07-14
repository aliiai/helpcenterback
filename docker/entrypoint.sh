#!/usr/bin/env sh
set -eu

cd /var/www/html

echo "[entrypoint] Waiting for MySQL at ${DB_HOST:-mysql}:${DB_PORT:-3306}..."

i=0
until php -r "
try {
    new PDO(
        sprintf('mysql:host=%s;port=%s', getenv('DB_HOST') ?: 'mysql', getenv('DB_PORT') ?: '3306'),
        getenv('DB_USERNAME') ?: 'helpdesk',
        getenv('DB_PASSWORD') ?: 'secret'
    );
    exit(0);
} catch (Throwable \$e) {
    exit(1);
}
" >/dev/null 2>&1; do
    i=$((i + 1))
    if [ "$i" -ge 60 ]; then
        echo "[entrypoint] MySQL did not become ready in time." >&2
        exit 1
    fi
    sleep 1
done

echo "[entrypoint] MySQL is ready."

if [ ! -f .env ]; then
    echo "[entrypoint] Creating .env from .env.example"
    cp .env.example .env
fi

# Generate APP_KEY only when missing — never overwrite an existing one.
if ! grep -Eq '^APP_KEY=base64:.+' .env; then
    echo "[entrypoint] Generating application key"
    php artisan key:generate --force --no-interaction
fi

if [ ! -d vendor ] || [ ! -f vendor/autoload.php ]; then
    echo "[entrypoint] Installing Composer dependencies"
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache || true

php artisan package:discover --ansi >/dev/null 2>&1 || true

if [ ! -L public/storage ] && [ ! -e public/storage ]; then
    echo "[entrypoint] Linking storage"
    php artisan storage:link --no-interaction || true
fi

echo "[entrypoint] Running migrations"
php artisan migrate --force --no-interaction

if [ "${APP_SEED:-false}" = "true" ]; then
    echo "[entrypoint] Seeding database (APP_SEED=true)"
    php artisan db:seed --force --no-interaction
fi

echo "[entrypoint] Starting: $*"
exec "$@"
