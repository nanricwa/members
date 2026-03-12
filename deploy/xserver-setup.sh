#!/bin/bash
# ============================================================
# Xserver デプロイスクリプト（初回セットアップ）
# ============================================================
# 使い方: SSH接続後に実行
#   chmod +x xserver-setup.sh && ./xserver-setup.sh
#
# 前提:
#   - XserverでSSHが有効化済み
#   - GitHubリポジトリにpush済み
#   - Xserverのサーバーパネルでphp 8.1以上が選択済み
# ============================================================

set -e

# === 設定 ===
GITHUB_REPO="YOUR_GITHUB_USERNAME/YOUR_REPO_NAME"  # ← 変更してください
DOMAIN="online-syukyaku.net"
SERVER_ID=$(whoami)  # Xserverのサーバーアカウント

DOMAIN_DIR="/home/${SERVER_ID}/${DOMAIN}"
APP_DIR="${DOMAIN_DIR}/laravel"
PUBLIC_HTML="${DOMAIN_DIR}/public_html"

echo "=== Xserver Laravel Deploy ==="
echo "Server ID: ${SERVER_ID}"
echo "Domain Dir: ${DOMAIN_DIR}"
echo "App Dir: ${APP_DIR}"
echo ""

# === 1. PHPバージョン確認 ===
echo "--- PHP Version ---"
php -v | head -1
echo ""

# === 2. Composerインストール（まだない場合） ===
if ! command -v ~/bin/composer &> /dev/null; then
    echo "--- Installing Composer ---"
    mkdir -p ~/bin
    cd ~/bin
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --install-dir=$HOME/bin --filename=composer
    php -r "unlink('composer-setup.php');"
    echo 'export PATH="$HOME/bin:$PATH"' >> ~/.bash_profile
    source ~/.bash_profile
    echo "Composer installed."
else
    echo "--- Composer already installed ---"
fi
echo ""

# === 3. Git clone ===
if [ -d "$APP_DIR" ]; then
    echo "--- App directory exists, pulling latest ---"
    cd "$APP_DIR"
    git pull origin main
else
    echo "--- Cloning repository ---"
    git clone "https://github.com/${GITHUB_REPO}.git" "$APP_DIR"
    cd "$APP_DIR"
fi
echo ""

# === 4. Composer install ===
echo "--- Composer install ---"
cd "$APP_DIR"
php ~/bin/composer install --no-dev --optimize-autoloader
echo ""

# === 5. .env 設定 ===
if [ ! -f "${APP_DIR}/.env" ]; then
    echo "--- Creating .env from production example ---"
    cp "${APP_DIR}/.env.production.example" "${APP_DIR}/.env"
    php artisan key:generate
    echo ""
    echo "!!! .env ファイルを編集してください !!!"
    echo "   ${APP_DIR}/.env"
    echo "   - DB_DATABASE / DB_USERNAME / DB_PASSWORD"
    echo "   - MAIL_USERNAME / MAIL_PASSWORD"
    echo "   - STRIPE_KEY / STRIPE_SECRET / STRIPE_WEBHOOK_SECRET"
    echo ""
    read -p "Press Enter after editing .env..."
fi
echo ""

# === 6. ストレージディレクトリ権限 ===
echo "--- Setting permissions ---"
chmod -R 775 "${APP_DIR}/storage"
chmod -R 775 "${APP_DIR}/bootstrap/cache"
echo ""

# === 7. マイグレーション ===
echo "--- Running migrations ---"
cd "$APP_DIR"
php artisan migrate --force
echo ""

# === 8. キャッシュ最適化 ===
echo "--- Caching config/routes/views ---"
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo ""

# === 9. ストレージリンク ===
echo "--- Creating storage link ---"
php artisan storage:link
echo ""

# === 10. public_html に index.php と .htaccess を配置 ===
echo "--- Setting up public_html ---"

# 既存のpublic_htmlの中身をバックアップ
if [ -f "${PUBLIC_HTML}/index.html" ]; then
    mv "${PUBLIC_HTML}/index.html" "${PUBLIC_HTML}/index.html.bak"
fi

# index.php をコピー（Laravelのpublicパスを書き換え済みのもの）
cat > "${PUBLIC_HTML}/index.php" << 'PHPEOF'
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Laravelプロジェクトのパス
$laravelPath = dirname(__DIR__) . '/laravel';

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $laravelPath . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $laravelPath . '/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $laravelPath . '/bootstrap/app.php';

$app->handleRequest(Request::capture());
PHPEOF

# .htaccess をコピー
cp "${APP_DIR}/public/.htaccess" "${PUBLIC_HTML}/.htaccess" 2>/dev/null || true

# favicon, robots.txt など静的ファイルもコピー
cp "${APP_DIR}/public/favicon.ico" "${PUBLIC_HTML}/" 2>/dev/null || true
cp "${APP_DIR}/public/robots.txt" "${PUBLIC_HTML}/" 2>/dev/null || true

# CSS/JS ディレクトリをシンボリックリンク
ln -sfn "${APP_DIR}/public/css" "${PUBLIC_HTML}/css"
ln -sfn "${APP_DIR}/public/js" "${PUBLIC_HTML}/js"

# storage リンクを public_html 内にも作成
ln -sfn "${APP_DIR}/storage/app/public" "${PUBLIC_HTML}/storage"

echo ""

# === 11. Filament アセットの公開 ===
echo "--- Publishing Filament assets ---"
cd "$APP_DIR"
php artisan filament:assets
# Filamentのアセットもpublic_htmlにリンク
if [ -d "${APP_DIR}/public/css/filament" ]; then
    ln -sfn "${APP_DIR}/public/css/filament" "${PUBLIC_HTML}/css/filament" 2>/dev/null || true
fi
if [ -d "${APP_DIR}/public/js/filament" ]; then
    ln -sfn "${APP_DIR}/public/js/filament" "${PUBLIC_HTML}/js/filament" 2>/dev/null || true
fi
echo ""

# === 12. Queue テーブル作成（初回のみ） ===
echo "--- Queue table ---"
php artisan queue:table 2>/dev/null || true
php artisan migrate --force 2>/dev/null || true
echo ""

echo "============================================"
echo "  Setup Complete!"
echo "============================================"
echo ""
echo "Next steps:"
echo "  1. ブラウザで https://${DOMAIN} にアクセスして確認"
echo "  2. https://${DOMAIN}/admin でFilament管理画面を確認"
echo "  3. CRONを設定:"
echo "     Xserverサーバーパネル > CRON設定 > 追加:"
echo "     */5 * * * * cd ${APP_DIR} && php artisan schedule:run >> /dev/null 2>&1"
echo ""
echo "  4. Stripe Webhook URL を設定:"
echo "     https://${DOMAIN}/webhook/stripe"
echo ""
echo "  5. 管理者ユーザーを作成:"
echo "     cd ${APP_DIR} && php artisan make:filament-user"
echo ""
