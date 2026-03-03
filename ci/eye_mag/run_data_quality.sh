#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT_DIR"

DB_HOST="${EYE_MAG_DB_HOST:-}"
DB_PORT="${EYE_MAG_DB_PORT:-3306}"
DB_USER="${EYE_MAG_DB_USER:-}"
DB_PASS="${EYE_MAG_DB_PASS:-}"
DB_NAME="${EYE_MAG_DB_NAME:-}"

if [[ -z "$DB_HOST" || -z "$DB_USER" || -z "$DB_NAME" ]]; then
  echo "Skipping data quality checks: EYE_MAG_DB_HOST/EYE_MAG_DB_USER/EYE_MAG_DB_NAME not configured."
  exit 0
fi

if ! command -v mysql >/dev/null 2>&1; then
  echo "mysql client not found; skipping data quality checks."
  exit 0
fi

echo "Running EyeMag nightly data quality checks..."

MYSQL_PWD="$DB_PASS" mysql \
  --host="$DB_HOST" \
  --port="$DB_PORT" \
  --user="$DB_USER" \
  --database="$DB_NAME" \
  --batch --raw --silent \
  < ci/eye_mag/data_quality_checks.sql \
  > /tmp/eye_mag_data_quality_results.tsv

cat /tmp/eye_mag_data_quality_results.tsv

fail=0
while IFS=$'\t' read -r check_name issue_count; do
  if [[ -z "$check_name" ]]; then
    continue
  fi
  if [[ "$issue_count" =~ ^[0-9]+$ ]] && [[ "$issue_count" -gt 0 ]]; then
    echo "FAILED: ${check_name} has ${issue_count} issues."
    fail=1
  fi
done < /tmp/eye_mag_data_quality_results.tsv

if [[ "$fail" -ne 0 ]]; then
  exit 1
fi

echo "EyeMag data quality checks passed."
