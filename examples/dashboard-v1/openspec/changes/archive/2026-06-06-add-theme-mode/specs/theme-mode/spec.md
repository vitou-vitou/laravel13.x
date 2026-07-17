# theme-mode

## Requirements

### Requirement: Three appearance modes

The application SHALL offer Light, Dark, and Auto (system) appearance modes on authenticated and guest Breeze layouts.

#### Scenario: User selects dark mode

- **WHEN** the user activates Dark on the theme toggle
- **THEN** `localStorage.theme` is set to `dark`
- **AND** the `dark` class is applied to `document.documentElement`

#### Scenario: User selects auto mode

- **WHEN** the user activates Auto on the theme toggle
- **THEN** `localStorage.theme` is set to `system`
- **AND** appearance follows `prefers-color-scheme`

### Requirement: Filament compatibility

The Breeze theme preference SHALL use the same `localStorage.theme` key and values as Filament (`light`, `dark`, `system`).

#### Scenario: Admin panel theme switcher

- **WHEN** an admin opens `/admin`
- **THEN** Filament's built-in theme switcher is available
- **AND** changes sync with the Breeze frontend preference
