#!/usr/bin/env bash
set -euo pipefail
jsp_dir="$(cd "$(dirname "$0")" && pwd)"
repo_dir="$(cd "$jsp_dir/.." && pwd)"
cd "$repo_dir"
# Local PostgreSQL settings match the database service in compose.yaml.
export MYBOOK_SKIP_DOTENV=1 APP_ENV=local APP_DEBUG=false APP_LOCALE=en APP_FALLBACK_LOCALE=en
export APP_TIMEZONE="${APP_TIMEZONE:-Asia/Jakarta}"
export APP_KEY="${APP_KEY:-$(php -r 'echo "base64:".base64_encode(random_bytes(32));')}"
export DB_CONNECTION=pgsql
export DB_HOST="${DB_HOST:-127.0.0.1}" DB_PORT="${DB_PORT:-5432}"
export DB_DATABASE="${DB_DATABASE:-mybook}" DB_USERNAME="${DB_USERNAME:-postgres}"
export DB_PASSWORD="${DB_PASSWORD-postgres}"
export CACHE_STORE=file SESSION_DRIVER=file QUEUE_CONNECTION=sync
export MYBOOK_BACKEND=laravel MYBOOK_API_URL=http://127.0.0.1:8000/api/jsp
export MYBOOK_JSP_URL="http://localhost:${PORT:-8080}"
if [ ! -f vendor/autoload.php ]; then composer install --no-scripts --no-interaction; fi
if ! php -r 'exit(extension_loaded("pdo_pgsql") ? 0 : 1);'; then
    printf 'The PHP pdo_pgsql extension is not installed.\n' >&2
    exit 1
fi
if ! php -r '
    try {
        new PDO(
            "pgsql:host=".getenv("DB_HOST").";port=".getenv("DB_PORT").";dbname=".getenv("DB_DATABASE").";connect_timeout=3",
            getenv("DB_USERNAME"),
            getenv("DB_PASSWORD"),
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
        );
    } catch (PDOException $e) {
        fwrite(STDERR, "PostgreSQL is unavailable. Run docker compose up -d db or check DB_HOST/DB_PORT/DB_DATABASE/DB_USERNAME/DB_PASSWORD.\n");
        exit(1);
    }
'; then
    exit 1
fi
php artisan package:discover --no-ansi >/dev/null
php artisan migrate --force --no-ansi
php artisan db:seed --class=JspDemoSeeder --force --no-ansi
cd "$jsp_dir"
mvn -B -q verify
cd "$repo_dir"
php -S 127.0.0.1:8000 -t public public/index.php &
laravel_pid=$!
trap 'kill "$laravel_pid" 2>/dev/null || true' EXIT INT TERM
cd "$jsp_dir"
printf '\nOpen website: %s\nPort 8000 is used by the Laravel API.\n\n' "$MYBOOK_JSP_URL"
java -Duser.timezone="$APP_TIMEZONE" -cp 'target/classes:target/dependency/*' id.ac.its.mybook.Main
