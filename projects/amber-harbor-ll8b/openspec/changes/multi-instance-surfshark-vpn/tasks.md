# Tasks: multi-instance-surfshark-vpn

## Phase 0 — Prerequisites (manual, one-time)

- [ ] P0.1 Install Surfshark APK on LDPlayer template (index 0)
- [ ] P0.2 Log in Surfshark; connect USA; accept VPN permission dialog
- [ ] P0.3 Confirm TikTok + `basicSettings.adbDebug=1` on template
- [ ] P0.4 Document template index in `settings.json` → `fleet.templateIndex`

## Phase 1 — Fleet registry & multi-ADB

- [x] T1.1 `FleetRegistry` + `data/fleet.json` schema (index, name, adbPort, lastIp, lastCountry, vpnOkAt)
- [x] T1.2 `LdPlayer.ListInstances()` from `list2` (port from `velvet-forge` pattern)
- [x] T1.3 `AdbClient` bind to `127.0.0.1:{5555+2*index}` per session
- [x] T1.4 CLI `--index N` parsing on all commands
- [x] T1.5 `fleet init` — `ldconsole copy` loop + `LdConfig.EnsureAdbEnabled` per index
- [x] T1.6 `fleet status` — table of index/running/adb/ip/country
- [ ] T1.7 Unit tests: port math, registry load/save

## Phase 2 — VPN orchestration

- [x] T2.1 `VpnOrchestrator` — launch Surfshark, detect connected state via UI dump
- [x] T2.2 `vpn ensure` — connect USA if disconnected; handle permission dialog once
- [x] T2.3 Persist `vpnOkAt` in fleet registry after success
- [ ] T2.4 Integration test on index 0 (manual gate)

## Phase 3 — Location verify

- [x] T3.1 `LocationVerifier` — `curl`/`wget` inside emulator via adb shell
- [x] T3.2 Parse `country` from ipinfo JSON; fail if ≠ `US`
- [x] T3.3 Wire into `PreflightFlow` and `CycleFlow` (gate before TikTok)

## Phase 4 — Fleet operations

- [x] T4.1 `launch --index N` with stagger
- [x] T4.2 `PreflightFlow` / `DoctorFlow` multi-index
- [x] T4.3 README + `docs/FLEET_VPN.md`
- [ ] T4.4 Real-case: 3-instance parallel verify (user machine)

## Phase 5 — Optional (post v1)

- [ ] T5.1 LDMultiPlayer window tiling helper
- [ ] T5.2 `fleet up --max-active 5` queue for 20 registered instances
- [ ] T5.3 Per-instance Surfshark server rotation
