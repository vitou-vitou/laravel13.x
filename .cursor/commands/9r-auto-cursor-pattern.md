# /9r-auto-cursor-pattern — Cursor-Style Smart Router

Auto-picks best provider + model across your 15–20 providers using Cursor's routing ladder.

## Trigger

Run `/9r-auto-cursor-pattern [message]` or attach skill `9r-auto-cursor-pattern`.

## Strategy

1. **Speed Tier (<1s)**: `ag/gemini-3.7-flash-high`, `groq/llama-3.3-70b-versatile`, `kiro/claude-haiku-4.5`.
2. **Balanced Tier (2–4s)**: `kiro/claude-sonnet-4.5`, `kiro/deepseek-3.2`, `openrouter/anthropic/claude-3.5-sonnet`.
3. **Premium Tier (4–8s)**: `cursor-ide/claude-3-5-sonnet`, `cursor-ide/o1-preview`, `claude-code/claude-3-opus`.
