#!/usr/bin/env bash
# scripts/migrate.sh — Laravel 마이그레이션 실행
set -euo pipefail

FRESH="${1:-}"

if [[ "$FRESH" == "--fresh" ]]; then
    echo "[zslab] Running migrate:fresh --seed ..."
    docker compose exec api php artisan migrate:fresh --seed --force
else
    echo "[zslab] Running migrate ..."
    docker compose exec api php artisan migrate --force
fi

echo "[zslab] Migration complete."
