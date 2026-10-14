# Manager Library API

Implementación inicial de HU-01 en Laravel 11.

## Ejecutar

```bash
docker compose build
docker compose run --rm api php artisan migrate --seed
docker compose up
```

Alternativa, dentro de un contenedor PHP/Composer:

```bash
docker run --rm -v "${PWD}:/app" -w /app composer:2 install --no-interaction --prefer-dist
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Rutas verificables:

- `GET /api/v1/health` → `{"status":"ok"}`.
- `POST /api/v1/auth/login` → JWT HS256 de 8 horas.
- `GET /api/v1/auth/me` → requiere `Authorization: Bearer <token>`.
- `GET /api/v1/admin-only` → requiere token con rol `admin`.

La HU no se marca como validada automáticamente: en el entorno de desarrollo actual no están
instalados PHP/Composer y Docker no tiene el daemon activo. La ejecución final debe registrar
la salida real de estos comandos antes de confirmar la HU.
