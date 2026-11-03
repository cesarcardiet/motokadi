# Motokadi API v1 (Flutter)

Base URL local: `http://127.0.0.1:8010/api/v1`

Auth: Bearer token (Laravel Sanctum). Login con las mismas cuentas de la web.

## Públicos
- `GET /health`
- `GET /catalog/motorcycles`
- `GET /catalog/motorcycles/{id}`
- `GET /catalog/branches`
- `POST /auth/login` `{ email, password, device_name? }`

## Autenticados
- `GET /auth/me`
- `POST /auth/logout`
- `GET|POST /applications` · `GET /applications/{id}`
- `POST /applications/{id}/documents/{document}`
- `GET /contracts` (incluye moto asignada: cartel/placa/serial)
- `GET /vouchers` · `GET /vouchers/{id}/pdf`
- `POST /installments/{id}/vouchers`
- Staff: `/staff/applications...`
- Admin: `/admin/payments...`

La app Flutter vive en `mobile/` y consume este catálogo (con fallback local).
