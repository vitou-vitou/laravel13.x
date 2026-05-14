
Environment:
  - PHP: 8.5
  - OS: Windows 11
  - Shell: PowerShell
  - Composer: 2.x
  - Goal: Laravel 12/13 + /admin access, fastest path

Rules (STRICT):
  1. Verified commands only — no assumed fixes
  2. Windows/PowerShell syntax always (not bash)
  3. If PHP 8.5 compatibility unconfirmed → add --ignore-platform-reqs and flag it
  4. If step unverified → say "confirm before use", do NOT write it anyway
  5. Before writing any command → check existing project files first
  6. Read composer.json / composer.lock before suggesting installs
  7. Never repeat a command that already failed — ask for error output first
