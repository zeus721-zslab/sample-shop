#!/usr/bin/env bash
# scripts/restart-api.sh
# FrankenPHP Worker 모드에서 코드 변경 후 적용 시 사용
set -euo pipefail

echo "[zslab] Restarting API container (FrankenPHP worker reload)..."
docker compose restart api

echo "[zslab] Clearing Laravel caches..."
docker compose exec api php artisan route:clear
docker compose exec api php artisan config:clear
docker compose exec api php artisan cache:clear

echo "[zslab] API restart complete."
