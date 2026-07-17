## 1. Data layer

- [x] 1.1 Migration `tunnels` + `Tunnel` model with domain validation
- [x] 1.2 `config/tunnel.php` + `TUNNEL_ADMIN_ENABLED`

## 2. Services

- [x] 2.1 `EnvFileWriter`, `NgrokEnvSync`, `TunnelActivator`, `TunnelHealthChecker`

## 3. Filament + RBAC

- [x] 3.1 `TunnelResource` (CRUD, Activate, Verify, OAuth URLs)
- [x] 3.2 `manage_dev_tunnels` in `RolePermissionSeeder`

## 4. Tests + docs

- [x] 4.1 Feature + unit tests
- [x] 4.2 `NEXT_SESSION.md` + `.env.example`
