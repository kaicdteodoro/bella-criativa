#!/usr/bin/env bash

set -Eeuo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

REMOTE="${DEPLOY_REMOTE:-origin}"
BRANCH="${DEPLOY_BRANCH:-main}"
PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"
ALLOW_BRANCH_MISMATCH="${DEPLOY_ALLOW_BRANCH_MISMATCH:-0}"
CPANEL_GIT_MANAGER="${CPANEL_GIT_MANAGER:-0}"

maintenance_enabled=0

restore_site() {
  local exit_code=$?

  if [[ "$maintenance_enabled" == "1" && "$exit_code" != "0" ]]; then
    echo "Deploy failed. Bringing the application back online..."
    "$PHP_BIN" artisan up || true
  fi

  exit "$exit_code"
}

trap restore_site EXIT

log() {
  printf '\n==> %s\n' "$1"
}

require_command() {
  if ! command -v "$1" >/dev/null 2>&1; then
    echo "Missing required command: $1" >&2
    exit 1
  fi
}

require_command git
require_command "$PHP_BIN"
require_command "$COMPOSER_BIN"

log "Checking repository"

current_branch="$(git rev-parse --abbrev-ref HEAD)"

if [[ "$current_branch" != "$BRANCH" && "$ALLOW_BRANCH_MISMATCH" != "1" ]]; then
  echo "Current branch is '$current_branch', expected '$BRANCH'." >&2
  echo "Set DEPLOY_ALLOW_BRANCH_MISMATCH=1 only if this is intentional." >&2
  exit 1
fi

if ! git diff --quiet || ! git diff --cached --quiet; then
  echo "Tracked files have local changes. Stop and inspect before deploying:" >&2
  git status --short
  exit 1
fi

log "Fetching $REMOTE/$BRANCH"
git fetch "$REMOTE" "$BRANCH"

if ! git merge-base --is-ancestor HEAD "$REMOTE/$BRANCH"; then
  echo "Local HEAD is not an ancestor of $REMOTE/$BRANCH. A fast-forward deploy is not safe." >&2
  git status --short
  exit 1
fi

log "Enabling maintenance mode"
"$PHP_BIN" artisan down --render="errors::503" || true
maintenance_enabled=1

log "Updating code"
git pull --ff-only "$REMOTE" "$BRANCH"

log "Installing PHP dependencies"
"$COMPOSER_BIN" install --no-dev --prefer-dist --optimize-autoloader --no-interaction

log "Running migrations"
"$PHP_BIN" artisan migrate --force

log "Refreshing Laravel caches"
"$PHP_BIN" artisan optimize:clear
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache

if "$PHP_BIN" artisan list --raw | grep -qx 'responsecache:clear'; then
  log "Clearing response cache"
  "$PHP_BIN" artisan responsecache:clear
fi

log "Bringing application online"
"$PHP_BIN" artisan up
maintenance_enabled=0

log "Deploy finished"

if [[ "$CPANEL_GIT_MANAGER" == "1" ]]; then
  cat <<'EOF'

Important:
CPANEL_GIT_MANAGER=1 is enabled. If this repository is managed by cPanel
Git Version Manager and the public site is only updated after the panel's
Deploy/Upload action, open cPanel and run that action now.

EOF
fi
