# JWT API Test Results

**Server:** `http://localhost:8001`  
**Credentials:** `test@example.com` / `password`

---

## Endpoints

| Endpoint | Method | Result |
|---|---|---|
| `/api/auth/login` | POST | ✅ Returns JWT token |
| `/api/auth/me` | GET | ✅ Returns user data |
| `/api/auth/refresh` | POST | ✅ Returns new token |
| `/api/auth/logout` | POST | ✅ `{"message":"Logged out"}` |
| `/api/auth/me` (no token) | GET | ⚠️ Returns HTML without `Accept: application/json` |

---

## Sample Requests

### Login
```bash
curl -s -X POST http://localhost:8001/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'
```
**Response:**
```json
{
  "access_token": "<jwt>",
  "token_type": "bearer",
  "expires_in": 3600
}
```

### Get Authenticated User
```bash
curl -s http://localhost:8001/api/auth/me \
  -H "Authorization: Bearer <access_token>" \
  -H "Accept: application/json"
```
**Response:**
```json
{
  "id": 1,
  "name": "Test User",
  "email": "test@example.com",
  "email_verified_at": null,
  "created_at": "2026-05-14T09:12:59.000000Z",
  "updated_at": "2026-05-14T09:12:59.000000Z"
}
```

### Refresh Token
```bash
curl -s -X POST http://localhost:8001/api/auth/refresh \
  -H "Authorization: Bearer <access_token>" \
  -H "Accept: application/json"
```
**Response:**
```json
{
  "access_token": "<new_jwt>",
  "token_type": "bearer",
  "expires_in": 3600
}
```

### Logout
```bash
curl -s -X POST http://localhost:8001/api/auth/logout \
  -H "Authorization: Bearer <access_token>" \
  -H "Accept: application/json"
```
**Response:**
```json
{"message":"Logged out"}
```

---

## Notes

- Always send `Accept: application/json` — without it, unauthenticated requests return HTML redirect instead of 401 JSON.
- Wrong credentials return `{"message":"Invalid credentials"}`.
- Token expiry: 3600 seconds (1 hour).
- App uses SQLite (`database/database.sqlite`). Run `php artisan db:seed` to recreate test user.
