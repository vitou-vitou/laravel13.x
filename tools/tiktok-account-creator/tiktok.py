import importlib
import sys

print(
    "tiktok.py: prefer research_loop.py --ack-research-only (diagnose + batch + playbook)",
    file=sys.stderr,
    flush=True,
)

import bot

while True:
    importlib.reload(bot)
    bot.main()
