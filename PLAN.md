# Plan de trabajo — Manager Library

## Alcance y fuente de verdad

El desarrollo se hará en `testtechnical`, con React en frontend, Laravel/PHP en backend,
PostgreSQL 16 y Docker Compose. El negocio, las historias, criterios de aceptación,
modelo de datos, contrato HTTP, arquitectura y ADR se toman de
`test-manager-library-3145555-docs/spec-python/`; sus decisiones de lenguaje/framework se
traducen al stack del reto.

Fuentes consultadas: `constitution.md`, `spec.md`, `plan.md`, `architecture.md`,
`arquitectura-panoramica.md`, `data-model.md`, `api-contract.md`, `tasks.md` y
`adr/adr-001` a `adr-004`.

## Flujo Git obligatorio

1. `main` es la base y `dev` nace de `main`.
2. Para cada historia se crea, en orden, `hu-01-dev`, `hu-02-dev`, …, `hu-10-dev` desde `dev`.
3. En la rama de la HU se implementa, se prueba, se documenta la evidencia y se hace solo el
   commit de esa HU.
4. La rama se sube a `origin` y se espera confirmación de que la HU es correcta.
5. Solo después de la confirmación se hace merge de la rama hija a `dev` y se crea la siguiente.
6. No se saltan HUs ni se mezclan cambios de una HU futura en la rama actual.

## Orden funcional y trazabilidad

| Rama | HU | Resultado mínimo | Tareas relacionadas |
|---|---|---|---|
| `hu-01-dev` | Iniciar sesión | JWT de 8 h, 401 uniforme, protección/autorización por rol y `/health` | T-01, T-06, T-07, T-14, T-16, T-17 |
| `hu-02-dev` | Alta de bibliotecarios | Usuario `librarian`, username normalizado/único, nunca exponer clave | T-06, T-07, T-12, T-14, T-16 |
| `hu-03-dev` | Gestionar libros | CRUD administrativo, ISBN, ejemplares, portada y baja lógica | T-04, T-08, T-12, T-15, T-16, T-18 |
| `hu-04-dev` | Buscar libros | Búsqueda paginada por texto/categoría, activos y total | T-03, T-08, T-12, T-16 |
| `hu-05-dev` | Gestionar socios | Alta/listado/búsqueda paginada, documento único, sin borrado | T-06, T-12, T-16 |
| `hu-06-dev` | Registrar préstamo | 1–3 líneas, límites, vencidos, descuento atómico y valores congelados | T-05, T-08, T-09, T-13, T-16 |
| `hu-07-dev` | Devolución | Devolución idempotentemente protegida, stock y cierre calculado | T-05, T-10, T-13, T-16 |
| `hu-08-dev` | Consultar/listar préstamos | Detalle y filtros por fecha/estado/socio; estado no almacenado | T-05, T-10, T-16 |
| `hu-09-dev` | Libros vencidos | Listado de líneas vencidas, días de retraso y orden descendente | T-10, T-16 |
| `hu-10-dev` | Más prestados | Agregado por valores congelados, fechas válidas y solo admin | T-11, T-16 |

## Ejecución acelerada en menos de una hora

La prioridad es dejar evidencia ejecutable del núcleo antes de ampliar cobertura. Cada bloque
termina con prueba real, commit y subida solamente en la rama de la HU actualmente confirmada.

### 00–05 · Preparación

- Confirmar `main`, crear/verificar `dev` y revisar estado limpio.
- Crear estructura Laravel/React/infra sin duplicar migraciones fuera del backend.
- Preparar `.env.example`, Docker Compose, PostgreSQL y variables `DATABASE_URL`, `JWT_SECRET`,
  `ADMIN_USERNAME`, `ADMIN_PASSWORD`, `CORS_ORIGIN` sin guardar secretos.

### 05–12 · Base técnica

- Laravel arranca y responde `GET /api/v1/health` con `{"status":"ok"}`.
- Crear migración del esquema `library`, las seis tablas, FKs, índices, categorías fijas y
  `CHECK` de ejemplares; el administrador inicial se crea desde configuración de arranque.
- Preparar manejo único de errores (`code`, `message`, `details`) y respuestas `snake_case`.

### 12–17 · HU-01 — autenticación

- Implementar login con Argon2id y JWT HS256 de 8 horas, con `sub` y `role` únicamente.
- Devolver `INVALID_CREDENTIALS` sin revelar si falló usuario o clave.
- Proteger endpoints y diferenciar 401/403.
- Evidencia: health, login válido, login inválido, ausencia de token y rol insuficiente.

### 17–21 · HU-02 — bibliotecarios

- Crear usuario solo con rol `librarian`, normalizar username y rechazar duplicados/admin.
- Evidencia: 201 sin password/hash, 409 duplicado y 422 para rol admin.

### 21–29 · HU-03 — libros

- Crear/editar/listar detalle/baja lógica y portadas JPG/PNG hasta 2 MB con `cover_key` opaca.
- Validar ISBN, total y disponibilidad; impedir baja con préstamos abiertos.
- Evidencia: CRUD, 409 ISBN, 422 total/archivo y 404 de libro dado de baja.

### 29–33 · HU-04 — búsqueda

- Búsqueda parcial de título/autor, categoría, activos, orden por título y paginación máx. 50.
- Evidencia: filtros, `total`, límites de `page/size` y 404 de inexistente.

### 33–37 · HU-05 — socios

- Alta, búsqueda y listado paginado; documento único y sin endpoint de borrado.
- Evidencia: 201, 409 duplicado y conteo de préstamos activos en detalle.

### 37–45 · HU-06 — préstamo

- Implementar transacción para validar socio, vencidos, máximo 3, libros únicos y stock.
- Crear 1–3 líneas, restar disponibilidad y congelar título/ISBN/categoría; usar control
  optimista/versionado para el último ejemplar.
- Evidencia: 201, 422 por límites/duplicado, 409 por stock/vencido y rollback completo.

### 45–49 · HU-07 — devolución

- Devolver una línea, incrementar stock una sola vez y calcular cierre cuando corresponda.
- Evidencia: devolución válida y segundo intento con `ITEM_ALREADY_RETURNED`.

### 49–53 · HU-08 y HU-09 — consultas operativas

- Detalle/listado de préstamos con filtros y estado calculado `open/overdue/closed`.
- Listar vencidos sin devolver, con días de retraso y orden descendente.
- Evidencia: consulta por rango/socio/estado y endpoint de vencidos.

### 53–56 · HU-10 — reporte

- Consulta agregada por `book_id + book_title + category_name` congelados, rango inclusivo,
  orden descendente y solo admin.
- Evidencia: rango invertido 422, rango vacío sin error y estabilidad tras renombrar catálogo.

### 56–60 · integración y entrega

- Ejecutar pruebas unitarias, integración, contrato, concurrencia y recorrido mínimo frontend.
- Confirmar Docker Compose, README, `git status`, ausencia de secretos y evidencias reproducibles.
- Subir únicamente la rama HU terminada; hacer merge a `dev` solo con confirmación explícita.

## Criterios transversales de terminado

- Arquitectura hexagonal: dominio sin dependencias de Laravel; composición en un único punto.
- Migraciones son propietarias del esquema; `infra` solo levanta motor/red/volúmenes.
- PostgreSQL impone PK, FK, unicidad y rango de disponibilidad; el dominio impone reglas de negocio.
- Préstamos/devoluciones no se borran; libros usan baja lógica; estados se calculan.
- Fechas en UTC, endpoints bajo `/api/v1`, errores uniformes y mensajes en español.
- No se registran ni devuelven contraseñas, hashes, tokens en logs, secretos ni trazas.
- La evidencia de cada HU queda en su commit y en el registro de pruebas del README.

## Estado inicial

- Rama base: `main`.
- Rama de trabajo creada: `dev`.
- Próximo paso, pendiente de confirmación: crear `hu-01-dev` e implementar HU-01.
