#!/usr/bin/env bash
# bootstrap-app.sh — one-time setup after user-data.sh has run.
# Run via SSM or SSH as ubuntu on the EC2 instance.
#
# Required environment variables:
#   REPO_URL       — git clone URL (e.g. git@github.com:org/taller-php.git)
#   APP_KEY        — Laravel app key (generate with: php artisan key:generate --show)
#   DB_HOST        — RDS endpoint hostname
#   DB_PASSWORD    — PostgreSQL password
#   REVERB_APP_KEY — Reverb app key
#   REVERB_APP_SECRET — Reverb app secret
#
# Optional:
#   DEPLOY_REF     — branch/tag to checkout (default: main)
#   DB_DATABASE    — database name (default: taller_php)
#   DB_USERNAME    — database user (default: postgres)
set -euo pipefail

APP_DIR="/var/www/taller-php"
PHP_VERSION="8.4"
DEPLOY_REF="${DEPLOY_REF:-main}"

: "${REPO_URL:?REPO_URL is required}"
: "${APP_KEY:?APP_KEY is required}"
: "${DB_HOST:?DB_HOST is required}"
: "${DB_PASSWORD:?DB_PASSWORD is required}"
: "${REVERB_APP_KEY:?REVERB_APP_KEY is required}"
: "${REVERB_APP_SECRET:?REVERB_APP_SECRET is required}"

DB_DATABASE="${DB_DATABASE:-taller_php}"
DB_USERNAME="${DB_USERNAME:-postgres}"

# ── Public IP for nip.io domain ──────────────────────────────────────────────
PUBLIC_IP=$(curl -sf --max-time 5 http://169.254.169.254/latest/meta-data/public-ipv4 || echo "")
APP_URL="https://${PUBLIC_IP}.nip.io"

# ── Clone repository ─────────────────────────────────────────────────────────
if [ ! -d "${APP_DIR}/.git" ]; then
  git clone "${REPO_URL}" "${APP_DIR}"
fi
cd "${APP_DIR}"
git fetch --all --prune
git checkout -B "${DEPLOY_REF}" "origin/${DEPLOY_REF}"
git reset --hard "origin/${DEPLOY_REF}"

chown -R ubuntu:www-data "${APP_DIR}"
chmod -R 755 "${APP_DIR}"

# ── Create .env ───────────────────────────────────────────────────────────────
cat > "${APP_DIR}/.env" <<EOF
APP_NAME=Laravel
APP_ENV=production
APP_KEY=${APP_KEY}
APP_DEBUG=false
APP_URL=${APP_URL}

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

DB_CONNECTION=pgsql
DB_HOST=${DB_HOST}
DB_PORT=5432
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}
DB_SSLMODE=require

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=reverb
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis

CACHE_STORE=redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

REVERB_APP_ID=taller-php-prod
REVERB_APP_KEY=${REVERB_APP_KEY}
REVERB_APP_SECRET=${REVERB_APP_SECRET}
REVERB_HOST=0.0.0.0
REVERB_PORT=6001
REVERB_SCHEME=https

MAIL_MAILER=log

FRONTEND_URL=REPLACE_WITH_CLOUDFRONT_URL
EOF

chmod 640 "${APP_DIR}/.env"
chown ubuntu:www-data "${APP_DIR}/.env"

# ── Install PHP dependencies ──────────────────────────────────────────────────
sudo -u ubuntu composer install \
  --no-interaction \
  --prefer-dist \
  --no-dev \
  --optimize-autoloader \
  --working-dir="${APP_DIR}"

# ── Storage & bootstrap dirs ─────────────────────────────────────────────────
mkdir -p "${APP_DIR}/storage/framework/cache/data" \
         "${APP_DIR}/storage/framework/sessions" \
         "${APP_DIR}/storage/framework/views" \
         "${APP_DIR}/bootstrap/cache"
chown -R ubuntu:www-data "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache"
chmod -R 775 "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache"

# ── Laravel setup ────────────────────────────────────────────────────────────
cd "${APP_DIR}"
sudo -u ubuntu php artisan storage:link
sudo -u ubuntu php artisan migrate --force
sudo -u ubuntu php artisan config:cache
sudo -u ubuntu php artisan route:cache
sudo -u ubuntu php artisan view:cache

# ── Nginx ─────────────────────────────────────────────────────────────────────
cp "${APP_DIR}/deploy/aws/nginx/taller-php.conf" /etc/nginx/sites-available/taller-php.conf
ln -sf /etc/nginx/sites-available/taller-php.conf /etc/nginx/sites-enabled/taller-php.conf
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx

# ── Systemd services ─────────────────────────────────────────────────────────
for svc in taller-php-queue taller-php-reverb taller-php-scheduler; do
  cp "${APP_DIR}/deploy/aws/systemd/${svc}.service" /etc/systemd/system/ 2>/dev/null || true
done
cp "${APP_DIR}/deploy/aws/systemd/taller-php-scheduler.timer" /etc/systemd/system/ 2>/dev/null || true

systemctl daemon-reload
systemctl enable --now taller-php-queue
systemctl enable --now taller-php-reverb
systemctl enable --now taller-php-scheduler.timer

echo ""
echo "✓ Bootstrap complete"
echo "  Backend URL: ${APP_URL}"
echo "  Update FRONTEND_URL in ${APP_DIR}/.env and re-run: php artisan config:cache"
