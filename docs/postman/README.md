# Postman kits

Import these into Postman Desktop (or use the cloud collections already created).

## JWT (`examples/jwt`)

- `jwt/jwt-auth.collection.json`
- `jwt/jwt-local.environment.json`
- App: `php artisan serve --port=8001`
- Seeded user: `test@example.com` / `password`

## Swagger Petstore (public demo)

- `petstore/petstore.swagger.json` — source OpenAPI from https://petstore.swagger.io/v2/swagger.json
- `petstore/petstore.collection.json` — generated collection
- `petstore/petstore.environment.json` — `baseUrl` + `api_key=special-key`
