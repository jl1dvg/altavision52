#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT_DIR"

echo "Running php -l for interface/forms/eye_mag..."
php_file_count=$(find interface/forms/eye_mag -type f -name "*.php" | wc -l | tr -d ' ')
if [[ "$php_file_count" -eq 0 ]]; then
  echo "No PHP files found in interface/forms/eye_mag"
  exit 0
fi

find interface/forms/eye_mag -type f -name "*.php" | sort | while IFS= read -r file; do
  php -n -d error_reporting=32767 -l "$file" >/dev/null
done

echo "PHP lint passed (${php_file_count} files)."
