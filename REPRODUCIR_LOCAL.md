# Reproducir el entorno localmente (Laravel + Reverb + Redis)

Resumen rápido
- Stack: Laravel (app), Redis (broker), Reverb (servidor WebSocket self-hosted).
- Recomendado: usar Docker Compose incluido en el repo.

1) Requisitos
- Docker & Docker Compose instalados.
- Puerto HTTP expuesto (Compose usa 8080) y puerto WS 6001.

2) Levantar todo con Docker (recomendado)
```bash
# build + detach
docker compose up -d --build

# verificar estado
docker compose ps
```

3) Comprobaciones básicas
```bash
# logs (sigue stdout)
docker compose logs -f nginx app websockets redis --no-log-prefix --tail 200

# entrar en el contenedor app
docker compose exec app bash
```

4) Preparar la app (desde el contenedor `app` o en host)
```bash
cp .env.example .env
php artisan key:generate
composer install
php artisan migrate --force
php artisan db:seed --force   # opcional
php artisan storage:link
```

5) Reverb / Websockets
- Compose ya corre `php artisan reverb:start --debug` en el servicio `websockets` (revisa `docker-compose.yml`).
- Si quieres arrancarlo manualmente dentro del contenedor `app`:
```bash
docker compose exec app php artisan reverb:start --host=0.0.0.0 --port=6001 --debug
```

6) Cola (broadcast jobs)
```bash
docker compose exec app php artisan queue:work --once --tries=1
# o para worker continuo
docker compose exec app php artisan queue:work
```

7) Probar un evento público (sin rutas HTTP de debug)
- Publicar desde `app` con un one-liner PHP:
```bash
docker compose exec app php -r "require 'vendor/autoload.php'; $p=new \\Pusher\\Pusher(getenv('REVERB_APP_KEY'), getenv('REVERB_APP_SECRET'), getenv('REVERB_APP_ID'), ['host'=>getenv('REVERB_HOST')?:'websockets','port'=>intval(getenv('REVERB_PORT')?:6001),'scheme'=>getenv('REVERB_SCHEME')?:'http','timeout'=>5]); $p->trigger('test-channel','manual-event',['message'=>'hello from manual publish']); echo \"sent\n\";"
```
- Suscribirte (ejemplo rápido con `wscat`):
```bash
npm i -g wscat
wscat -c "ws://localhost:6001/app/$(grep REVERB_APP_KEY .env | cut -d= -f2)?protocol=7&client=js&version=7.0.3"
# luego subscribe a 'test-channel' usando el protocolo pusher desde el cliente JS
```

8) Probar canales privados (resumen de pasos)
- Requisitos: `Sanctum` o sesión/cookie habilitada, `/broadcasting/auth` debe protegerse con middleware de autenticación.
- Pasos básicos:
  1. Crear usuario y obtener cookie de sesión o token Sanctum.
  2. Configurar `Laravel Echo`/`pusher-js` con `authEndpoint` apuntando a `/broadcasting/auth` y enviar credenciales.
  3. Suscribirte al canal privado `private-profesional.{id}` o `private-usuario.{id}`.

Ejemplo mínimo con `pusher-js` (cliente Node/Browser) — asume cookie de sesión válida:
```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.Pusher = Pusher;
const echo = new Echo({
  broadcaster: 'pusher',
  key: process.env.MIX_REVERB_APP_KEY,
  wsHost: 'localhost',
  wsPort: 6001,
  forceTLS: false,
  disableStats: true,
  authEndpoint: 'http://localhost:8080/broadcasting/auth',
  auth: { headers: { 'X-SANCTUM-TOKEN': '<token-si-usas>' } }
});

echo.private(`profesional.${id}`).listen('ReservationCreated', e => console.log(e));
```

9) Troubleshooting rápido
- `websockets` no arranca → comprobar que `pcntl` está disponible en la imagen PHP.
- Eventos no llegan → comprobar `docker compose logs websockets` y `docker compose logs app`.
- Redis unreachable → `docker compose ps` y `docker compose logs redis`.
- Si private channels fallan: revisar `routes/channels.php` y middleware en `BroadcastServiceProvider` (usar `auth:sanctum` o `auth` según tu flujo).

10) Restaurar rutas de debug (solo temporal)
- Si quieres volver a la ruta `/ _debug/publish-manual` o `/ _debug/emit-reservation`, puedo regenerarlas en `routes/web.php` para pruebas rápidas y luego quitarlas.

Archivo de comandos útiles: `RUN_COMMANDS.txt` (en la raíz) contiene comandos copiable.

Fin — sigue estos pasos y dime si quieres que implemente la autenticación privada con ejemplos listos para ejecutar.