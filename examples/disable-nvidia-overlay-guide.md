# How to Disable NVIDIA GeForce Overlay (FPS/GPU/CPU Monitor)

**Date:** 2026-05-10  
**Author:** VITOU  
**PC:** DESKTOP-FFLIO0B (ASUS TUF Gaming F17 FX707ZV4)

---

## Problem

The NVIDIA GeForce Overlay displays an FPS/GPU/CPU/LAT performance monitor on screen. This overlay appears on top of all applications and can be distracting during work.

## Solution — Permanently Disable the Overlay

### Step 1: Open NVIDIA App

Open the **NVIDIA App** from the Start menu or desktop shortcut.

### Step 2: Go to Settings

Click on **Settings** in the left sidebar (gear icon at the bottom).

### Step 3: Turn Off the Overlay

Under the **Overlay** section at the top:

- Toggle **"NVIDIA overlay"** to **OFF**
- The **"Game filters and Photo mode"** toggle will also become disabled automatically

This setting persists across reboots. The overlay shortcut **Alt+Z** will no longer work once disabled.

### Step 4: Verify

- The "NVIDIA GeForce Overlay DT" bar at the bottom of the screen should disappear immediately
- The FPS / GPU / CPU / LAT stats HUD will no longer appear when launching games or apps

## Additional Notes

- This does **not** uninstall the NVIDIA App — only disables the overlay feature
- If the overlay reappears after a driver update, repeat Steps 1–3
- The NVIDIA App does not appear in Windows Startup Apps — the overlay runs as part of NVIDIA's driver services
- To re-enable, simply toggle "NVIDIA overlay" back to ON in the same Settings page
- Keyboard shortcut reference: **Alt+Z** (open overlay), **Alt+R** (instant replay), **Alt+F3** (game filter) — all disabled when overlay is off

## Team Action

All team PCs with NVIDIA GPUs should have this overlay disabled to avoid interference during work and screen sharing. Follow the steps above on each machine.
