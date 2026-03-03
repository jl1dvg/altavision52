#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT_DIR"

echo "=== EyeMag Guard ==="
bash ci/eye_mag/php_lint_eye_mag.sh
bash ci/eye_mag/smoke_eye_mag_contracts.sh
bash ci/eye_mag/static_rule_scan.sh
echo "EyeMag Guard completed."
