#!/usr/bin/env bash
# Generate ${adjective}-${noun}-${randomid} project slugs (Shell 100%).
set -euo pipefail

ADJECTIVES=(amber brisk crisp lunar quiet silver velvet wild calm bold swift gentle bright cool warm)
NOUNS=(anchor beacon drift forge harbor relay signal spark vertex wave crest pulse orbit ridge stream)

random_slug() {
  local adj noun id
  adj="${ADJECTIVES[$((RANDOM % ${#ADJECTIVES[@]}))]}"
  noun="${NOUNS[$((RANDOM % ${#NOUNS[@]}))]}"
  id="$(printf '%x' $((RANDOM * 65536 + RANDOM)) | head -c 4)"
  echo "${adj}-${noun}-${id}"
}

if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
  random_slug
fi
