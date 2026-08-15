#!/bin/bash
set -e

# Zero-Downtime Deployment Script Blueprint
# Usage: ./deploy.sh

echo "🚀 Starting Zero-Downtime Deployment..."

# Directories
BASE_DIR="/var/www/suba_erp"
RELEASES_DIR="${BASE_DIR}/releases"
SHARED_DIR="${BASE_DIR}/shared"
CURRENT_DIR="${BASE_DIR}/current"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
NEW_RELEASE_DIR="${RELEASES_DIR}/${TIMESTAMP}"
REPO_URL="git@github.com:your-organization/suba-erp.git"

echo "📂 Creating new release directory: ${NEW_RELEASE_DIR}"
mkdir -p "$NEW_RELEASE_DIR"

echo "📥 Cloning repository..."
git clone --depth 1 "$REPO_URL" "$NEW_RELEASE_DIR"

echo "🔗 Linking shared storage & .env..."
ln -s "${SHARED_DIR}/.env" "${NEW_RELEASE_DIR}/.env"
rm -rf "${NEW_RELEASE_DIR}/storage"
ln -s "${SHARED_DIR}/storage" "${NEW_RELEASE_DIR}/storage"

cd "$NEW_RELEASE_DIR"

echo "📦 Installing composer dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

echo "🏗️ Building frontend assets..."
npm ci
npm run build

echo "🗄️ Running database migrations..."
php artisan migrate --force

echo "🧹 Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🔄 Swapping symlink to activate new release (ZERO DOWNTIME)..."
ln -sfn "$NEW_RELEASE_DIR" "$CURRENT_DIR"

echo "♻️ Restarting PHP-FPM & Supervisor Queue Workers..."
sudo systemctl reload php8.3-fpm
sudo supervisorctl restart all

echo "🗑️ Cleaning up old releases (Keeping last 3)..."
cd "$RELEASES_DIR" && ls -t | tail -n +4 | xargs -r rm -rf

echo "✅ Deployment Successful!"
