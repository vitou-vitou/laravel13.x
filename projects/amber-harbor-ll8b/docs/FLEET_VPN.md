# Fleet + Surfshark VPN (multi-instance)

OpenSpec: `openspec/changes/multi-instance-surfshark-vpn/`

## Phase 0 (manual, template index 0)

Automated via **`setup`** (recommended):

```bash
bin/amber-harbor-ll8b setup --index 0
```

Or manual:

1. Install **Surfshark** from Play Store and log in.
2. Connect to **United States** once; accept Android VPN permission (“Always allow”).
3. Install **TikTok**; leave `basicSettings.adbDebug=1` in `leidian0.config`.
4. Optional: set quick-connect to USA so clones inherit app state.

## Phase 1 — Register fleet

```bash
bin/amber-harbor-ll8b fleet init --count 10
```

Clones `templateIndex` (default 0) to indices `1..N-1`, patches ADB per instance, writes `data/fleet.json`.

## Phase 2 — Per-instance VPN + location

```bash
bin/amber-harbor-ll8b vpn ensure --index 3
bin/amber-harbor-ll8b location verify --index 3
```

## Phase 3 — Launch fleet

```bash
bin/amber-harbor-ll8b fleet launch --count 10
```

Stagger defaults to 5s (`fleet.launchStaggerSeconds` in settings).

## Phase 4 — Farm cycle on instance N

```bash
bin/amber-harbor-ll8b cycle --index 3 --ack-research-only
```

Preflight runs VPN + US location check unless `--skip-vpn`.

## ADB ports

| Index | Port   | Serial              |
|-------|--------|---------------------|
| 0     | 5555   | emulator-5554       |
| 1     | 5557   | emulator-5556       |
| N     | 5555+2N| emulator-(5554+2N) |

## Registry

`data/fleet.json` stores last IP, country, VPN OK timestamp per index.
