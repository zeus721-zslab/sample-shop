#!/usr/bin/env bash
# scripts/shell-api.sh — Laravel API 컨테이너 쉘 접속
set -euo pipefail

docker compose exec api sh "$@"
