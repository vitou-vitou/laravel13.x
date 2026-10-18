# win-disk-cleaner

Free, open-source Windows C: drive cleanup skill for AI coding agents (Claude Code, etc.).
Replaces paid "system optimizer" software with a modular PowerShell script and curated free tool recommendations.

## What it does

- **10-module PowerShell cleanup script** with DryRun preview mode
- Covers: temp files, recycle bin, Windows Update cache, browser caches (Edge/Chrome/Firefox), system logs, app caches (WeChat/QQ/Teams/Discord/Slack), developer caches (npm/pip/yarn/JetBrains/Gradle), hibernation, WinSxS, restore points
- **Free tool recommendations**: BleachBit, WizTree, Everything, dupeGuru, etc.
- Safe by default: preview before delete, skip flags for risky operations

## Installation

### Option 1: npx skills (Recommended)

```bash
npx skills add orzcls/win-disk-cleaner
```

> [skills CLI](https://github.com/eze-is/skills-cli) is an open-source Agent Skill package manager that auto-detects your Agent environment and installs to the correct location.

### Option 2: Let Agent auto-install

```
Install this skill: https://github.com/orzcls/win-disk-cleaner
```

### Option 3: Plugin install (Claude Code)

```bash
claude plugin marketplace add https://github.com/orzcls/win-disk-cleaner
claude plugin install win-disk-cleaner@win-disk-cleaner --scope user
```

### Option 4: Manual

```bash
git clone https://github.com/orzcls/win-disk-cleaner ~/.claude/skills/win-disk-cleaner
```

### Verify

After installation, restart Claude Code. The skill auto-triggers when you ask:

- "Clean my C drive"
- "Free up disk space on Windows"
- "Help me delete temp files"
- "C盘清理" / "清理系统垃圾"

## Standalone script usage (no AI Agent needed)

You can also use the PowerShell script directly:

```powershell
# Preview mode (safe, no deletion)
.\scripts\disk_cleaner.ps1 -DryRun

# Full cleanup
.\scripts\disk_cleaner.ps1

# Selective cleanup
.\scripts\disk_cleaner.ps1 -SkipHibernation -SkipWinSxS -SkipRestorePoints
```

**Requires**: Run as Administrator on Windows 10/11.

## CDR mode configuration

To use with Claude Desktop Router (CDR) mode, add to your Agent config:

```json
{
  "skills": {
    "win-disk-cleaner": {
      "source": "github:orzcls/win-disk-cleaner",
      "auto_update": true
    }
  }
}
```

## Skill structure

```
win-disk-cleaner/
├── SKILL.md                    # AI agent instructions & workflow
├── scripts/
│   └── disk_cleaner.ps1        # PowerShell cleanup script (10 modules)
├── references/
│   └── free_tools.md           # Curated free/open-source tool list
├── install.sh                  # One-liner installer (macOS/Linux)
├── install.ps1                 # One-liner installer (Windows)
└── README.md
```

## Cleanup modules

| #    | Module                               | Typical Space | Safe?                            |
| ---- | ------------------------------------ | ------------- | -------------------------------- |
| 1    | Temp files                           | 1-3 GB        | Yes                              |
| 2    | Recycle Bin                          | 0.5-2 GB      | Yes                              |
| 3    | Windows Update cache                 | 1-3 GB        | Yes                              |
| 4    | Browser caches                       | 0.5-2 GB      | Yes                              |
| 5    | System logs & cache                  | 0.5-1 GB      | Yes                              |
| 6    | App caches (QQ/WeChat/Teams/etc.)    | 0.2-1 GB      | Yes                              |
| 7    | Developer caches (npm/pip/JetBrains) | 0.5-5 GB      | Yes (re-downloads)               |
| 8    | Hibernation (hiberfil.sys)           | RAM size      | Use -SkipHibernation if needed   |
| 9    | WinSxS (DISM cleanup)                | 1-5 GB        | Yes (official MS method)         |
| 10   | Old restore points                   | 1-5 GB        | Use -SkipRestorePoints if needed |

## Recommended free tools

| Tool         | Use Case                        | Link                               |
| ------------ | ------------------------------- | ---------------------------------- |
| BleachBit    | Automatic cleaner (open-source) | https://www.bleachbit.org/         |
| WizTree      | Fastest disk analyzer           | https://diskanalyzer.com/          |
| Everything   | Instant file search             | https://www.voidtools.com/         |
| dupeGuru     | Duplicate file finder           | https://dupeguru.voltaicideas.net/ |
| SpaceSniffer | Visual disk map                 | https://www.intosoftware.com/      |

## License

MIT
