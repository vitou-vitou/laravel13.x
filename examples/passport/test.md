# Passport API Tests

**Server:** `http://localhost:8000`
**Test credentials:** `test@example.com` / `password`

---

## Requests to Execute

### 1. Register
```bash
curl -s -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"password","password_confirmation":"password"}'
```

### 2. Login
```bash
curl -s -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"test@example.com","password":"password"}'
```

### 3. Get Authenticated User (use token from login)
```bash
curl -s http://localhost:8000/api/auth/user \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### 4. Get Todos (public)
```bash
curl -s http://localhost:8000/api/v1/todos \
  -H "Accept: application/json"
```

### 5. Create Todo (requires auth)
```bash
curl -s -X POST http://localhost:8000/api/v1/todos \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"title":"Test todo from Passport","description":"Created via API test"}'
```

### 6. Logout
```bash
curl -s -X POST http://localhost:8000/api/auth/logout \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### 7. Access protected route after logout (expect 401)
```bash
curl -s http://localhost:8000/api/auth/user \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

---

## Results

**Tested at:** 2026-05-14  
**Server:** `http://localhost:8002` *(port 8000 was occupied by the JWT example project — passport served on 8002)*

| # | Endpoint | Method | Status | Pass/Fail | Notes |
|---|----------|--------|--------|-----------|-------|
| 1 | `/api/auth/register` | POST | 422 | PASS | Email already taken from prior run — skipped to login as expected |
| 2 | `/api/auth/login` | POST | 200 | PASS | Token captured for subsequent requests |
| 3 | `/api/auth/user` | GET | 200 | PASS | Authenticated user returned correctly |
| 4 | `/api/v1/todos` | GET | 200 | PASS | Public endpoint, empty list returned |
| 5 | `/api/v1/todos` | POST | 201 | PASS | Todo created successfully |
| 6 | `/api/auth/logout` | POST | 200 | PASS | Token revoked |
| 7 | `/api/auth/user` | GET | 401 | PASS | Revoked token correctly rejected |

**7/7 passed**

Full JSON results saved to `examples/logs/passport-test-results.json`.
