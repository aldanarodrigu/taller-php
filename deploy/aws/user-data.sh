#!/usr/bin/env bash
set -euo pipefail

export DEBIAN_FRONTEND=noninteractive
APP_DIR="/var/www/taller-php"
PHP_VERSION="8.4"

apt-get update
apt-get install -y software-properties-common ca-certificates curl git unzip nginx redis-server
add-apt-repository -y ppa:ondrej/php
apt-get update
apt-get install -y \
  php${PHP_VERSION}-cli \
  php${PHP_VERSION}-fpm \
  php${PHP_VERSION}-common \
  php${PHP_VERSION}-mbstring \
  php${PHP_VERSION}-xml \
  php${PHP_VERSION}-curl \
  php${PHP_VERSION}-zip \
  php${PHP_VERSION}-pgsql \
  php${PHP_VERSION}-bcmath \
  php${PHP_VERSION}-intl \
  php${PHP_VERSION}-redis \
  php${PHP_VERSION}-pcntl

if ! command -v composer >/dev/null 2>&1; then
  curl -fsSL https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

systemctl enable --now php${PHP_VERSION}-fpm
systemctl enable --now nginx
systemctl enable --now redis-server

mkdir -p "${APP_DIR}"
chown -R ubuntu:ubuntu "${APP_DIR}"

cat >/etc/sudoers.d/taller-php <<'EOF'
ubuntu ALL=(root) NOPASSWD: /bin/systemctl restart php8.4-fpm, /bin/systemctl restart nginx, /bin/systemctl restart taller-php-queue, /bin/systemctl restart taller-php-reverb, /bin/systemctl daemon-reload
EOF
chmod 440 /etc/sudoers.d/taller-php
