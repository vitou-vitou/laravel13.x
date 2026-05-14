# Fix Missing PCNTL Extension in PHP 8.4 with Herd

Enable pcntl extension via Herd CLI:

```bash
herd php:extensions enable pcntl
```

Verify available extensions:

```bash
herd php:extensions list
```

Check active PHP version:

```bash
herd php:list
```

## Note

Some Herd builds don't include pcntl in compile flags. If missing entirely, switch to a PHP version that has it or rebuild via Homebrew/native install.

## Context

PCNTL (Process Control) is needed by:
- Laravel Horizon (queue supervisor)
- Some worker processes
- Fork-based operations
