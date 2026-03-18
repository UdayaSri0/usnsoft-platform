#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT_DIR"

if [[ -f .env ]]; then
  set -a
  source .env
  set +a
fi

BACKUP_DIR="${1:-$ROOT_DIR/storage/app/backups/database}"
TIMESTAMP="$(date -u +%Y%m%dT%H%M%SZ)"
OUTPUT_FILE="$BACKUP_DIR/postgres-$TIMESTAMP.sql.gz"

mkdir -p "$BACKUP_DIR"

docker compose exec -T postgres \
  pg_dump -U "${DB_USERNAME:-usnsoft}" -d "${DB_DATABASE:-usnsoft}" \
  | gzip -c > "$OUTPUT_FILE"

echo "Database backup created: $OUTPUT_FILE"
