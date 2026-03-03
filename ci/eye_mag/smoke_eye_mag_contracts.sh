#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT_DIR"

assert_contains() {
  local pattern="$1"
  local file="$2"
  local message="$3"
  if ! rg -n --pcre2 "$pattern" "$file" >/dev/null; then
    echo "FAILED: $message"
    exit 1
  fi
}

assert_not_contains() {
  local pattern="$1"
  local file="$2"
  local message="$3"
  if rg -n --pcre2 "$pattern" "$file" >/dev/null; then
    echo "FAILED: $message"
    exit 1
  fi
}

echo "Running EyeMag source smoke contracts..."

assert_contains 'requestOwner\s*!==\s*\(string\)\s*\$lock\['\''LOCKEDBY'\''\]' "interface/forms/eye_mag/save.php" "Unlock path must validate current LOCKEDBY owner."
assert_contains "sqlBeginTrans\\(\\)" "interface/forms/eye_mag/save.php" "store_IMPPLAN must begin SQL transaction."
assert_contains "sqlCommitTrans\\(\\)" "interface/forms/eye_mag/save.php" "store_IMPPLAN must commit SQL transaction."
assert_contains "sqlRollbackTrans\\(\\)" "interface/forms/eye_mag/save.php" "store_IMPPLAN must rollback on failure."
assert_contains "UPDATE billing SET activity = 0 WHERE encounter = \\? AND pid = \\?" "interface/forms/eye_mag/save.php" "code_visit must retire only scoped billing rows."
assert_not_contains "delete\\s+from\\s+billing\\s+where\\s+encounter\\s*=\\?" "interface/forms/eye_mag/save.php" "code_visit must not hard-delete all encounter billing rows."

assert_not_contains "newtype\\('Eye Meds'\\)" "interface/forms/eye_mag/a_issue.php" "a_issue must not force Eye Meds on load."
assert_contains 'newtype\(<\?php echo js_escape\(\$initial_issue_type\); \?>\);' "interface/forms/eye_mag/a_issue.php" "a_issue must honor initial issue type."

assert_contains '\$canEditIssues\s*=\s*acl_check' "interface/forms/eye_mag/view.php" "view must use ACL-backed canEditIssues flag."
assert_contains "function normalizeIMPPLANCodeList" "interface/forms/eye_mag/js/eye_impplan_helpers.php" "IMPPLAN code normalization helper must exist."
assert_contains "normalizeIMPPLANItems\\(items\\)" "interface/forms/eye_mag/js/eye_base.php" "IMPPLAN builder must normalize incoming items."
assert_contains "placeholder='<\\?php echo xla\\('Puedes escribir observaciones y plan para este diagnostico'\\); \\?>'" "interface/forms/eye_mag/js/eye_base.php" "PLAN textarea must keep guidance placeholder."

echo "EyeMag source smoke contracts passed."
