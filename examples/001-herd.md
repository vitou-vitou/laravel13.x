# Laravel Herd — Windows 11 Quickstart

## What Herd includes
- PHP, nginx, Node.js, Composer, Laravel installer
- No separate installs needed

---

## 1. Download + install Herd

Download from [herd.laravel.com](https://herd.laravel.com/) → run installer as admin.

> Admin required: installer adds HerdHelper service to update hosts file + map `.test` domains.

---

## 2. Create fresh Laravel project

**A) Herd dashboard UI:**
Open Herd → "New Laravel Project" → pick starter kit or none

**B) Terminal:**
```powershell
cd ~\Herd
laravel new myappadmin
```

---

## 3. Access site

```
http://myappadmin.test
```

> No `php artisan serve` needed. Auto-served via nginx.
> URL = folder name + `.test`
> Sites live in `%USERPROFILE%\Herd\`

---

## Key differences vs manual install

| | Manual | Herd |
|---|---|---|
| Serve | `php artisan serve` | automatic |
| URL | `localhost:8000` | `appname.test` |
| PHP version | system PHP | managed via Herd UI |
| nginx | no | yes (built-in) |

---

## Performance tip

Add `%USERPROFILE%\.config\herd` to Windows Defender exclusions — prevents Defender scanning from slowing Herd.

```powershell
Add-MpPreference -ExclusionPath "$env:USERPROFILE\.config\herd"
```
