#!/usr/bin/env bash
# scripts/up.sh — 운영 환경 기동
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "[zslab] Starting production stack..."
docker compose up -d --build --remove-orphans
echo "[zslab] Done. Use 'docker compose logs -f' to watch logs."
