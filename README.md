<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Docker

Este proyecto incluye una pila de desarrollo con PHP-FPM, Nginx, MySQL y Redis.

Para levantarla:

```bash
docker compose up --build
```

La aplicación quedará disponible en `http://localhost:8080`.

Si es la primera vez, Laravel generará el archivo `.env` y la clave de aplicación al iniciar el contenedor `app`.
Después de levantar los contenedores, ejecuta las migraciones con `docker compose exec app php artisan migrate`.

## CI/CD

Este repositorio incluye workflows de GitHub Actions para automatizar validación y despliegue:

- `CI`: corre en cada `push` y `pull_request`, instala dependencias y ejecuta `composer test`.
- `Deploy`: se ejecuta en `push` a `main` o manualmente, y despliega por SSH en un servidor que ya tenga el repositorio clonado.

Para el despliegue debes definir estos secretos en GitHub:

- `DEPLOY_HOST`
- `DEPLOY_USER`
- `DEPLOY_PATH`
- `DEPLOY_SSH_KEY`

El pipeline de despliegue asume que el servidor puede ejecutar `git`, `composer` y `php artisan`.
En `workflow_dispatch` podés pasar `ref` para desplegar una rama, tag o commit específico.

## Deploy en EC2

La guía operativa está en `deploy/aws/README.md`.

Las plantillas para levantar Laravel en una instancia Ubuntu están en `deploy/aws/`:

- `deploy/aws/user-data.sh`: instala PHP, Nginx, Redis y Composer.
- `deploy/aws/systemd/taller-php-queue.service`: worker de colas.
- `deploy/aws/systemd/taller-php-reverb.service`: servidor Reverb.
- `deploy/aws/systemd/taller-php-scheduler.timer`: scheduler de Laravel.
- `deploy/aws/nginx/taller-php.conf`: virtual host para `public/`.

Usa esas plantillas como base y adapta el usuario, la ruta del proyecto y el dominio antes de aplicar el deploy.

## Frontend separado

El despliegue del frontend está desacoplado y se ejecuta desde el repositorio `taller-php-frontend`.
Este backend solo despliega API, workers y WebSockets.

Si el frontend se publica en `S3/CloudFront`, el backend solo debe exponer API y WebSockets.

Configura en producción:

- `APP_URL` con el dominio del backend.
- `FRONTEND_URL` o el dominio de CloudFront para CORS y redirecciones.
- `SANCTUM_STATEFUL_DOMAINS` con el dominio del frontend si usás autenticación por cookies.
- `VITE_API_URL` en el frontend apuntando al backend público.

Si más adelante querés servir el build desde Laravel, copiá `dist/` a `public/` antes del deploy y ajustá `nginx` para que el SPA resuelva con `index.php` o `index.html`.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
