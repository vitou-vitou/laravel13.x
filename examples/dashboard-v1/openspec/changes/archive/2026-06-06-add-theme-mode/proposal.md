## Why

Staff use the dashboard and Filament admin in different lighting. A shared light/dark/auto preference avoids eye strain and matches OS settings.

## What Changes

- Tailwind `darkMode: 'class'` on Breeze pages
- Light / Dark / Auto toggle in nav and guest auth layouts
- `localStorage.theme` values `light`, `dark`, `system` (Filament-compatible)
- Filament admin `->darkMode()` enabled

## Capabilities

### New

- `theme-mode`: persisted appearance with system auto detection
