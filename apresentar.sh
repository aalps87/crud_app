#!/usr/bin/env bash
# AERIS - baixa e roda o projeto no Linux ou Mac.
# Precisa de PHP 8.2+ (com pdo_sqlite, mbstring e fileinfo) e curl.
# Uso:  bash apresentar.sh
set -euo pipefail

REPO="https://github.com/aalps87/crud_app/archive/refs/heads/main.tar.gz"
COMPOSER_URL="https://getcomposer.org/download/latest-stable/composer.phar"
PORT="${PORT:-8000}"
HERE="$(cd "$(dirname "$0")" && pwd)"
if [ -f "$HERE/artisan" ]; then APP="$HERE"; else APP="$HERE/AERIS"; fi
TOOLS="${XDG_CACHE_HOME:-$HOME/.cache}/aeris-tools"

falha() { echo; echo "[ERRO] $*"; exit 1; }

command -v curl >/dev/null || falha "Instale o curl."
command -v php  >/dev/null || falha "Instale o PHP 8.2+ (Ubuntu: sudo apt install php-cli php-sqlite3 php-mbstring php-xml php-curl | Mac: brew install php)."
php -r "exit(version_compare(PHP_VERSION,'8.2','>=') && extension_loaded('pdo_sqlite') && extension_loaded('mbstring') && extension_loaded('fileinfo') ? 0 : 1);" \
    || falha "O PHP precisa ser 8.2+ com pdo_sqlite, mbstring e fileinfo."

echo "[1/5] Baixando o projeto..."
if [ ! -f "$APP/artisan" ]; then
    mkdir -p "$APP"
    curl -fsSL "$REPO" | tar -xz -C "$APP" --strip-components=1 || falha "Nao consegui baixar o projeto."
else
    echo "      Projeto ja esta aqui, pulando."
fi

echo "[2/5] Preparando o Composer..."
mkdir -p "$TOOLS"
[ -f "$TOOLS/composer.phar" ] || curl -fsSL -o "$TOOLS/composer.phar" "$COMPOSER_URL" || falha "Nao consegui baixar o Composer."

cd "$APP"
echo "[3/5] Instalando as dependencias (pode demorar)..."
[ -f vendor/autoload.php ] || php "$TOOLS/composer.phar" install --no-dev --no-interaction --prefer-dist --optimize-autoloader

echo "[4/5] Configurando e criando o banco com os dados de exemplo..."
[ -f .env ] || cp .env.example .env
grep -q '^APP_KEY=base64' .env || php artisan key:generate --force
[ -f database/database.sqlite ] || touch database/database.sqlite
php artisan migrate:fresh --seed --force
php artisan optimize:clear >/dev/null

echo "[5/5] Pronto!  http://localhost:$PORT   (Ctrl+C para desligar)"
echo "      Logins de teste: database/seeders/DefaultUserSeeder.php"
( sleep 2; (xdg-open "http://localhost:$PORT" || open "http://localhost:$PORT") >/dev/null 2>&1 || true ) &
php artisan serve --host=127.0.0.1 --port="$PORT"
