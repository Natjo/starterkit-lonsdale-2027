#!/bin/bash
# Bibliotheque partagee pour les scripts Docker du starterkit.
# Usage : source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/scripts/lib.sh"
#   ou depuis scripts/ : source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/lib.sh"

# ─── Couleurs ────────────────────────────────────────────────────────

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# ─── Resolution des chemins ──────────────────────────────────────────
# BASH_SOURCE[1] = le script qui a source lib.sh
# Fallback sur BASH_SOURCE[0] si appele directement (tests)

_CALLER="${BASH_SOURCE[1]:-${BASH_SOURCE[0]}}"
_CALLER_DIR="$(cd "$(dirname "$_CALLER")" && pwd)"

LIB_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DOCKER_DIR="$(dirname "$LIB_DIR")"
PROJECT_ROOT="$(dirname "$DOCKER_DIR")"

# Exporte SCRIPT_DIR vers le repertoire du script appelant
SCRIPT_DIR="$_CALLER_DIR"

ENV_FILE="$DOCKER_DIR/.env"
COMPOSE_FILE="$DOCKER_DIR/docker-compose.yml"

# ─── Chargement .env projet ─────────────────────────────────────────

load_env() {
  [ -f "$ENV_FILE" ] || { echo -e "${RED}Fichier .env introuvable : ${ENV_FILE}${NC}"; exit 1; }
  set -a
  source "$ENV_FILE"
  set +a
}

# ─── Chargement .env global (remontee des repertoires parents) ──────
# Ne source PAS le fichier (il peut contenir des noms invalides comme
# 1PASSWORD_*). Positionne GLOBAL_ENV vers le chemin trouve.

GLOBAL_ENV=""

find_global_env() {
  local dir="$PROJECT_ROOT"
  while [ "$dir" != "/" ]; do
    dir="$(dirname "$dir")"
    if [ -f "$dir/_infra/.env" ]; then
      GLOBAL_ENV="$dir/_infra/.env"
      return 0
    fi
    if [ -f "$dir/.env" ]; then
      GLOBAL_ENV="$dir/.env"
      return 0
    fi
  done
  return 1
}

# Lire une cle depuis le .env global (retourne la valeur brute, sans quotes)
global_env_get() {
  [ -n "$GLOBAL_ENV" ] || return 1
  local val
  val=$(grep "^${1}=" "$GLOBAL_ENV" 2>/dev/null | head -1 | cut -d'=' -f2-)
  val="${val#\'}" ; val="${val%\'}"
  val="${val#\"}" ; val="${val%\"}"
  echo "$val"
}

# ─── Spinner ─────────────────────────────────────────────────────────

SPINNER_PID=""
_SPINNER_FRAMES="⠋⠙⠹⠸⠼⠴⠦⠧⠇⠏"

start_spinner() {
  local msg="$1"
  (
    local i=0
    while true; do
      printf "\r  ${YELLOW}${_SPINNER_FRAMES:i%${#_SPINNER_FRAMES}:1}${NC} %s" "$msg"
      i=$((i + 1))
      sleep 0.08
    done
  ) &
  SPINNER_PID=$!
}

stop_spinner() {
  local result="${1:-}"
  local msg="${2:-}"
  if [ -n "$SPINNER_PID" ]; then
    kill "$SPINNER_PID" 2>/dev/null
    wait "$SPINNER_PID" 2>/dev/null || true
    SPINNER_PID=""
  fi
  printf "\r\033[K"
  case "$result" in
    ok)   echo -e "  ${GREEN}✓ ${msg}${NC}" ;;
    warn) echo -e "  ${YELLOW}⚠ ${msg}${NC}" ;;
    error) echo -e "  ${RED}✗ ${msg}${NC}" ;;
    "")   ;; # pas de message
  esac
}

# ─── Sed portable (macOS vs Linux) ──────────────────────────────────

portable_sed() {
  if [[ "$OSTYPE" == "darwin"* ]]; then
    sed -i '' "$@"
  else
    sed -i "$@"
  fi
}

# ─── Docker exec WP-CLI ─────────────────────────────────────────────

wp_exec() {
  docker exec -e 'WP_CLI_PHP_ARGS=-d error_reporting=E_ERROR' "${APP_NAME}-wp" "$@"
}

# ─── Ouverture navigateur ───────────────────────────────────────────

open_url() {
  local url="$1"
  if [[ "$OSTYPE" == "darwin"* ]]; then
    open "$url"
  elif command -v xdg-open &>/dev/null; then
    xdg-open "$url"
  fi
}
