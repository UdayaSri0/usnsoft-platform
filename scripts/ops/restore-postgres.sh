#!/usr/bin/env bash

set -euo pipefail

if [[ $# -lt 1 ]]; then
  echo "Usage: $0 <backup-file.sql.gz> [database-name]" >&2
  exit 1
fi

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT_DIR"

if [[ -f .env ]]; then
  set -a
  source .env
  set +a
fi

BACKUP_FILE="$1"
TARGET_DATABASE="${2:-${DB_DATABASE:-usnsoft}}"

if [[ ! -f "$BACKUP_FILE" ]]; then
  echo "Backup file not found: $BACKUP_FILE" >&2
  exit 1
fi

echo "Restoring $BACKUP_FILE into database $TARGET_DATABASE"
echo "Make sure you are not pointing at the wrong environment."

gunzip -c "$BACKUP_FILE" \
  | docker compose exec -T postgres \
      psql -v ON_ERROR_STOP=1 -U "${DB_USERNAME:-usnsoft}" -d "$TARGET_DATABASE"

echo "Restore completed."
