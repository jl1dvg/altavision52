#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT_DIR"

echo "Running EyeMag static rule scan..."

fail=0

scan_forbidden() {
  local pattern="$1"
  local target="$2"
  local description="$3"
  if rg -n --pcre2 "$pattern" $target >/tmp/eye_mag_static_scan_match.txt 2>/dev/null; then
    echo "FAILED: $description"
    cat /tmp/eye_mag_static_scan_match.txt
    fail=1
  fi
}

# Rule 1: avoid dangerous blanket billing deletes by encounter.
scan_forbidden "delete\\s+from\\s+billing\\s+where\\s+encounter\\s*=\\?" "interface/forms/eye_mag/save.php" "Forbidden: blanket delete from billing by encounter."

# Rule 2: avoid SQL/query string interpolation of request/session/user input variables.
scan_forbidden '(?i)(?:query|sql)\s*=.*\.\s*\$_(?:REQUEST|POST|GET|SESSION)' "interface/forms/eye_mag/save.php interface/forms/eye_mag/php/*.php" "Forbidden: SQL/query interpolation with request/session variables."

# Rule 3: avoid known unsafe lock release comment pattern.
scan_forbidden '//\s*&&\s*\(\$_REQUEST\['\''LOCKEDBY'\''\]\s*==\s*\$lock\['\''LOCKEDBY'\''\]\)' "interface/forms/eye_mag/save.php" "Forbidden: commented-out lock ownership check."

if [[ "$fail" -ne 0 ]]; then
  exit 1
fi

echo "EyeMag static rule scan passed."
