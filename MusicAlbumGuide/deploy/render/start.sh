#!/usr/bin/env sh
set -eu

: "${PORT:=10000}"

php artisan optimize:clear >/dev/null 2>&1 || true
php artisan storage:link >/dev/null 2>&1 || true
php artisan migrate --force

exec php artisan serve --host=0.0.0.0 --port="${PORT}"
