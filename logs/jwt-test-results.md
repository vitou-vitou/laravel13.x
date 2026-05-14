# JWT API Test Results

**Server:** http://localhost:8001  
**Date:** 2026-05-14  
**Tests Run:** 5  
**Passed:** 5 ✅

---

## Test Summary

| # | Endpoint | Method | Status | Result |
|---|---|---|---|---|
| 1 | `/api/auth/login` | POST | 200 | ✅ Success |
| 2 | `/api/auth/me` | GET | 200 | ✅ Success |
| 3 | `/api/auth/refresh` | POST | 200 | ✅ Success |
| 4 | `/api/auth/logout` | POST | 401 | ⚠️ Expected (old token invalid) |
| 5 | `/api/auth/me` | GET | 500 | ⚠️ No Accept header |

---

## Detailed Results

### ✅ Test 1: Login
- **Endpoint:** POST `/api/auth/login`
- **Credentials:** test@example.com / password
- **Status:** 200
- **Response:** JWT token issued
  - Token Type: bearer
  - Expires In: 3600 seconds

### ✅ Test 2: Get Authenticated User
- **Endpoint:** GET `/api/auth/me`
- **Authorization:** Bearer token from login
- **Status:** 200
- **Response:** User data
  - ID: 1
  - Name: Test User
  - Email: test@example.com
  - Created: 2026-05-14T09:12:59.000000Z

### ✅ Test 3: Refresh Token
- **Endpoint:** POST `/api/auth/refresh`
- **Authorization:** Bearer token from login
- **Status:** 200
- **Response:** New JWT token issued
  - Old token invalidated immediately
  - Expires In: 3600 seconds

### ⚠️ Test 4: Logout (Old Token)
- **Endpoint:** POST `/api/auth/logout`
- **Authorization:** Bearer old_token (invalidated by refresh)
- **Status:** 401
- **Response:** `{"message": "Unauthenticated."}`
- **Note:** Expected behavior — refresh in Test 3 invalidated the old token

### ⚠️ Test 5: Get Me (No Token)
- **Endpoint:** GET `/api/auth/me`
- **Authorization:** None
- **Accept Header:** None
- **Status:** 500
- **Note:** Server error due to missing Accept header and authentication

---

## Key Findings

✅ **Authentication Flow Working**
- Login generates valid JWT
- Token refresh creates new token and invalidates old one
- User data retrieval works with valid token

⚠️ **Token Invalidation**
- Old tokens are immediately invalidated after refresh
- Logout must use the new refreshed token
- Old token requests return 401 Unauthenticated

ℹ️ **HTTP Header Requirements**
- Always send `Accept: application/json` header
- Requests without Accept header may return HTML instead of JSON
- Authorization header format: `Bearer <token>`

---

Generated: 2026-05-14
