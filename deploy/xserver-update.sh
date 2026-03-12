#!/bin/bash
# ============================================================
# Xserver デプロイスクリプト（更新時）
# ============================================================
# コード更新時に実行
#   ./deploy/xserver-update.sh
# ============================================================

set -e

SERVER_ID=$(whoami)
DOMAIN="online-syukyaku.net"
APP_DIR="/home/${SERVER_ID}/${DOMAIN}/laravel"
PUBLIC_HTML="/home/${SERVER_ID}/${DOMAIN}/public_html"

cd "$APP_DIR"

echo "=== Pulling latest code ==="
git pull origin main

echo "=== Composer install ==="
php ~/bin/composer install --no-dev --optimize-autoloader

echo "=== Running migrations ==="
php artisan migrate --force

echo "=== Clearing and rebuilding cache ==="
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== Publishing Filament assets ==="
php artisan filament:assets

# 静的ファイルの再同期
cp "${APP_DIR}/public/favicon.ico" "${PUBLIC_HTML}/" 2>/dev/null || true
cp "${APP_DIR}/public/robots.txt" "${PUBLIC_HTML}/" 2>/dev/null || true

echo "=== Update complete! ==="
