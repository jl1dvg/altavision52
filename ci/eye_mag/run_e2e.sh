#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT_DIR"

if [[ "${EYE_MAG_E2E_ENABLED:-0}" != "1" ]]; then
  echo "Skipping EyeMag E2E: set EYE_MAG_E2E_ENABLED=1 to enable."
  exit 0
fi

required_vars=(
  EYE_MAG_BASE_URL
  EYE_MAG_USERNAME
  EYE_MAG_PASSWORD
  EYE_MAG_PID
  EYE_MAG_ENCOUNTER
  EYE_MAG_FORM_ID
)

for var_name in "${required_vars[@]}"; do
  if [[ -z "${!var_name:-}" ]]; then
    echo "Skipping EyeMag E2E: missing ${var_name}."
    exit 0
  fi
done

if ! command -v node >/dev/null 2>&1; then
  echo "Skipping EyeMag E2E: node is not installed."
  exit 0
fi

echo "Installing Playwright browser..."
npx --yes @playwright/test@1.52.0 install chromium

echo "Running EyeMag Playwright tests..."
npx --yes @playwright/test@1.52.0 test -c ci/eye_mag/e2e/playwright.config.js
