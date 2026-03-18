#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT_DIR"

BACKUP_DIR="${1:-$ROOT_DIR/storage/app/backups/storage}"
TIMESTAMP="$(date -u +%Y%m%dT%H%M%SZ)"
OUTPUT_FILE="$BACKUP_DIR/storage-$TIMESTAMP.tar.gz"

mkdir -p "$BACKUP_DIR"

tar -czf "$OUTPUT_FILE" \
  storage/app/private \
  storage/app/public

echo "Storage backup created: $OUTPUT_FILE"
