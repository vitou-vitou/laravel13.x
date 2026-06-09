# OCP Setup — Claude Pro in Cursor (Windows)

Use your Claude Pro/Max subscription inside Cursor IDE. Zero extra cost.

## Prerequisites

- Node.js 22.5+ (`node --version`)
- Git (`git --version`)
- [Cloudflared](https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/downloads/) installed
- [Claude Code CLI](https://docs.anthropic.com/en/docs/claude-cli) installed and logged in:
  ```
  npm install -g @anthropic-ai/claude-code
  claude auth login
  ```

## 1. Clone & Install

```powershell
git clone https://github.com/dtzp555-max/ocp.git %USERPROFILE%\ocp
cd %USERPROFILE%\ocp
npm install
```

## 2. Apply Windows Patches

OCP doesn't officially support Windows. Apply these 3 fixes:

### Patch 1 — `server.mjs`: Fix spawn for Windows

Find this line (~line 759):
```js
const proc = spawn(CLAUDE, cliArgs, { env, stdio: ["pipe", "pipe", "pipe"] });
```
Replace with:
```js
const proc = spawn(CLAUDE, cliArgs, { env, stdio: ["pipe", "pipe", "pipe"], shell: process.platform === "win32" });
```

### Patch 2 — `server.mjs`: Fix auth check for Windows

Find this line (~line 561):
```js
execFileSync(CLAUDE, ["auth", "status"], { encoding: "utf8", timeout: 10000, env });
```
Replace with:
```js
execFileSync(CLAUDE, ["auth", "status"], { encoding: "utf8", timeout: 10000, env, shell: process.platform === "win32" });
```

### Patch 3 — `server.mjs`: Fix command line length limit

Find `function buildCliArgs(cliModel, systemPrompt)` (~line 590) and replace the opening:
```js
function buildCliArgs(cliModel, systemPrompt) {
  const args = [
    "--model", cliModel,
    "--output-format", "stream-json",
    "--verbose",
    "--no-session-persistence",
    "--system-prompt", systemPrompt,
  ];
```
With:
```js
function buildCliArgs(cliModel, systemPrompt) {
  const WIN_MAX_SYSPROMPT = 4000;
  const effectivePrompt = (process.platform === "win32" && systemPrompt.length > WIN_MAX_SYSPROMPT)
    ? systemPrompt.slice(0, WIN_MAX_SYSPROMPT)
    : systemPrompt;

  const args = [
    "--model", cliModel,
    "--output-format", "stream-json",
    "--verbose",
    "--no-session-persistence",
    "--system-prompt", effectivePrompt,
  ];
```

### Patch 4 — `models.json`: Add GPT-5.x aliases

In `models.json`, add these to `legacyAliases`:
```json
"gpt-5.5": "claude-opus-4-8",
"gpt-5.4": "claude-opus-4-6",
"gpt-5.2": "claude-sonnet-4-6",
"gpt-5.4-mini": "claude-sonnet-4-6",
"gpt-5.4-nano": "claude-haiku-4-5-20251001",
"gpt-5.1": "claude-sonnet-4-6",
"gpt-5-mini": "claude-haiku-4-5-20251001"
```

## 3. Find your Claude CLI path

```powershell
(Get-Command claude).Source
# Example output: C:\Users\YOU\.vite-plus\bin\claude.cmd
# Use the .cmd path for CLAUDE_BIN
```

## 4. Create start script

Save as `start-ocp.bat` in your ocp folder:

```bat
@echo off
echo Starting OCP (Claude Pro Proxy for Cursor)...
powershell -ExecutionPolicy Bypass -Command ^
  "$env:CLAUDE_BIN = (Get-Command claude).Source;" ^
  "cd '%USERPROFILE%\ocp';" ^
  "$existing = netstat -ano | Select-String ':3456.*LISTEN';" ^
  "if ($existing) { $id = ($existing -split '\s+')[-1]; Stop-Process -Id $id -Force; Start-Sleep 2 };" ^
  "Start-Job { $env:CLAUDE_BIN = (Get-Command claude).Source; cd '%USERPROFILE%\ocp'; node server.mjs } | Out-Null;" ^
  "Start-Sleep 3;" ^
  "Write-Host 'OCP running on localhost:3456' -ForegroundColor Green;" ^
  "Write-Host 'Starting tunnel... copy URL below + add /v1 to paste in Cursor' -ForegroundColor Yellow;" ^
  "cloudflared tunnel --url http://127.0.0.1:3456"
pause
```

## 5. Configure Cursor

1. Open Cursor Settings → Models → API Keys
2. **OpenAI API Key** → toggle ON → enter any string (e.g. `ocp-local`)
3. **Override OpenAI Base URL** → toggle ON → paste tunnel URL + `/v1`
   Example: `https://xxx.trycloudflare.com/v1`
4. In chat, select any **GPT-5.x** model

## Model Mapping

| You select in Cursor | Actually runs |
|---|---|
| GPT-5.5 | Claude Opus 4.8 |
| GPT-5.4 | Claude Opus 4.7 |
| Codex 5.3 | Claude Opus 4.6 |
| GPT-5.2 | Claude Sonnet 4.6 |
| GPT-5.4 Mini | Claude Sonnet 4.6 |
| GPT-5.1 | Claude Sonnet 4.6 |
| GPT-5.4 Nano | Claude Haiku 4.5 |
| GPT-5 Mini | Claude Haiku 4.5 |

## Daily Usage

1. Run `start-ocp.bat`
2. Copy tunnel URL from output
3. Paste in Cursor → Override OpenAI Base URL (add `/v1`)
4. Code with Claude in Cursor!

## Notes

- Each person needs their own Claude Pro/Max subscription + `claude auth login`
- Tunnel URL changes each restart — update Cursor each time
- For permanent URL: set up [named Cloudflare tunnel](https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/) (free)
- Keep terminal open while using Cursor
