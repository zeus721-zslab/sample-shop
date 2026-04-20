#!/usr/bin/env bash
# scripts/down.sh — 전체 스택 중지
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "[zslab] Stopping stack..."
docker compose down
echo "[zslab] All containers stopped."
