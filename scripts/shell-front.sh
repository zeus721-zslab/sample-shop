#!/usr/bin/env bash
# scripts/shell-front.sh — Next.js 프론트 컨테이너 쉘 접속
set -euo pipefail

docker compose exec frontend sh "$@"
