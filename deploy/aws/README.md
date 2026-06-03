# Deploy en EC2

Guía rápida para poner Laravel + Reverb en una instancia Ubuntu.

## Requisitos

- Ubuntu 22.04/24.04
- PHP 8.4, Nginx, Redis y Composer instalados
- Repositorio clonado en `/var/www/taller-php`
- Secrets de GitHub configurados para el workflow de deploy

## Estructura

- `user-data.sh`: bootstrap inicial de paquetes
- `nginx/taller-php.conf`: virtual host
- `systemd/taller-php-queue.service`: worker de colas
- `systemd/taller-php-reverb.service`: servidor WebSocket
- `systemd/taller-php-scheduler.service`: ejecución del scheduler
- `systemd/taller-php-scheduler.timer`: timer del scheduler

## Secuencia recomendada

1. Lanzar la EC2 con `user-data.sh`.
2. Clonar el repo en `/var/www/taller-php`.
3. Copiar `.env` con la configuración de producción.
4. Instalar dependencias:

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan storage:link
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

5. Instalar y habilitar servicios:

```bash
sudo cp deploy/aws/nginx/taller-php.conf /etc/nginx/sites-available/taller-php.conf
sudo ln -sf /etc/nginx/sites-available/taller-php.conf /etc/nginx/sites-enabled/taller-php.conf
sudo nginx -t
sudo systemctl restart nginx

sudo cp deploy/aws/systemd/taller-php-queue.service /etc/systemd/system/
sudo cp deploy/aws/systemd/taller-php-reverb.service /etc/systemd/system/
sudo cp deploy/aws/systemd/taller-php-scheduler.service /etc/systemd/system/
sudo cp deploy/aws/systemd/taller-php-scheduler.timer /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable --now taller-php-queue
sudo systemctl enable --now taller-php-reverb
sudo systemctl enable --now taller-php-scheduler.timer
```

## Secrets del workflow

- `DEPLOY_HOST`
- `DEPLOY_USER`
- `DEPLOY_PATH`
- `DEPLOY_SSH_KEY`

## Notas

- Si el frontend va por `S3/CloudFront`, el backend solo necesita servir API y WebSockets.
- Si el frontend se sirve desde el backend, el build debe copiarse a `public/` antes del deploy.
- El puerto `6001` puede quedarse sin exponer públicamente si el acceso pasa por un proxy o balanceador.
