# Manager Library API

Implementación inicial de HU-01 en Laravel 11.

## Ejecutar

```bash
composer install
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
