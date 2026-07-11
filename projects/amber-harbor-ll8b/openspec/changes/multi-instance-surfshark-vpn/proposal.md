# Change: Multi-instance LDPlayer fleet + per-instance Surfshark USA VPN

**Project:** `amber-harbor-ll8b`  
**Mode:** OpenSpec (post-MVP) + Superpowers (TDD on apply)  
**Status:** PROPOSED — awaiting your confirmation before code

## Problem

Today `amber-harbor-ll8b` drives **one** LDPlayer index (`ldplayerIndex: 0`). TikTok farming at scale needs **10–20 parallel emulator screens**, each with:

1. **Persistent Surfshark VPN → USA** (remembered per LD instance)
2. **Launch by ID** — `amber-harbor-ll8b launch --index 7` opens instance 7 with VPN already connected
3. **Location gate** — every run verifies public IP geolocation is US before signup/login/post

## Goals

| # | Requirement |
|---|-------------|
| G1 | Manage fleet of N LDPlayer instances (default template clone, N=1..20) |
| G2 | Per-index profile: ADB port, VPN state, last verified IP/country, TikTok account binding |
| G3 | One-time golden template: TikTok + Surfshark + `adbDebug=1` + root |
| G4 | `vpn ensure --index N --country US` connects Surfshark inside emulator via ADB/UIAutomator |
| G5 | `location verify --index N` fails closed if country ≠ US (or configured region) |
| G6 | All existing flows accept `--index N` (preflight, cycle, signup, login, post) |
| G7 | Staggered launch + resource limits (720×1280, 2GB RAM) for 10–20 instances on one host |

## Non-goals (v1)

- Surfshark **host-level** Windows VPN (we use **Android app inside each emulator** so each instance has its own IP)
- Automatic Surfshark account creation / credential storage in git (secrets in local `settings.json` only)
- Cloud orchestration across multiple physical PCs

## Research summary (last30days-style + docs)

**LDPlayer multi-instance**

- `ldconsole add --name farm-03` or `copy --from 0 --name farm-03` ([LDPlayer CLI](https://www.ldplayer.net/blog/introduction-to-ldplayer-command-line-interface.html))
- ADB ports: `5555 + index×2` → index 0→5555, 1→5557, … 9→5573 ([ADB guide](https://codegive.com/blog/4_how_to_use_adb_to_connect_to_ldplayer_android_emulator.php))
- `list2` returns index, HWND, PID per instance
- LDMultiPlayer: clone player copies **installed apps + app data** → Surfshark login can persist if cloned after setup

**Surfshark on Android emulator**

- No reliable `adb shell` API to connect VPN without UI ([Stack Overflow VPN permission](https://stackoverflow.com/questions/67980851/grant-android-vpn-permission-via-command-line-using-adb))
- Pattern: UIAutomator + `input tap` (same as Windscribe automation projects)
- One-time **VPN permission dialog** must be accepted per instance (or inherited from clone)
- Package (install from Play Store / APK): `com.surfshark.vpnclient.android`

**Location verify**

- `adb shell curl -s https://ipinfo.io/json` or `am start` browser to ip-api; parse `country` == `US`
- Fallback: host curls through emulator is **not** valid — must run **inside** Android network stack (VPN-aware)

## Proposed architecture

```
┌─────────────────────────────────────────────────────────┐
│ amber-harbor-ll8b.exe                                   │
│  fleet init | fleet status | launch --index N           │
│  vpn ensure | location verify | cycle --index N         │
└────────────┬────────────────────────────────────────────┘
             │
    ┌────────┴────────┐
    ▼                 ▼
 FleetRegistry    InstanceSession(index)
 data/fleet.json   ├─ LdPlayer(index)
                    ├─ AdbClient(serial=127.0.0.1:5555+2i)
                    ├─ VpnOrchestrator (Surfshark UI)
                    └─ LocationVerifier (ipinfo JSON)
```

### Golden template workflow (one-time manual + automated clone)

1. Prepare **index 0** as `template`: ADB on, TikTok, Surfshark logged in, USA connected, VPN permission granted
2. `fleet init --count 10` → `ldconsole copy --from 0` × 9, patch each `leidian{N}.config` (`adbDebug`, unique MAC/IMEI via `modify`)
3. Store metadata in `data/fleet.json`

### Per-run flow (`cycle --index 5`)

1. `launch --index 5` if not running (stagger 5s between fleet-wide starts)
2. `adb connect 127.0.0.1:5565`
3. `vpn ensure --country US` — open Surfshark, tap Quick connect / USA if disconnected
4. `location verify` — must return `US` or abort
5. Existing preflight → signup → login → post

## CLI additions

```
fleet init --count 10 [--from-index 0]
fleet status
launch --index N
vpn ensure --index N [--country US]
location verify --index N [--country US]
cycle --index N --ack-research-only
```

Global: `--index N` on all existing commands (default from `settings.json` or 0).

## Settings (`settings.json`)

```json
{
  "ldplayerHome": "D:/LDPlayer/LDPlayer9",
  "ldplayerIndex": 0,
  "fleet": {
    "templateIndex": 0,
    "instanceCount": 10,
    "launchStaggerSeconds": 5,
    "vpn": {
      "package": "com.surfshark.vpnclient.android",
      "targetCountry": "US",
      "verifyUrl": "https://ipinfo.io/json"
    },
    "resolution": "720,1280,320",
    "memoryMb": 2048
  }
}
```

## Risks & mitigations

| Risk | Mitigation |
|------|------------|
| 20 instances OOM | Cap concurrent **running** instances; `fleet up --max-active 5`; lower DPI/RAM |
| Surfshark UI changes | UIAutomator text-first (`United States`, `Quick connect`); screenshot on failure |
| VPN permission per clone | Clone **after** permission granted on template; else `vpn grant` flow taps OK once |
| Same IP across instances | Surfshark rotating / different server per connect; verify unique IPs in `fleet status` |
| LDPlayer window tiling | Optional Phase 2: LDMultiPlayer Window Manager coords; v1 = launch headless-ish / user tiles |

## Definition of done (this change)

1. `fleet init --count 3` creates 3 instances with ADB + registry
2. `vpn ensure --index 1` + `location verify --index 1` → `country=US`
3. `cycle --index 1 --ack-research-only` completes on instance 1 while instance 0 idle
4. Reboot host, `launch --index 1` → Surfshark still logged in (clone persistence), verify passes

## Confirm to implement

Reply **approve multi-instance-vpn** (or `/opsx:apply multi-instance-surfshark-vpn`) to start TDD implementation.
