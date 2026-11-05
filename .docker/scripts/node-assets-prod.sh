#!/bin/bash
set -euo pipefail
cd "$(dirname "$0")/../.."
COMPOSE_FILE="${COMPOSE_FILE:-.docker/docker-compose.yml}"

# Keep production assets in place: restarting the watcher would overwrite them.
docker compose -f "$COMPOSE_FILE" stop nodejs
docker compose -f "$COMPOSE_FILE" run --rm --no-deps nodejs pnpm install --frozen-lockfile
docker compose -f "$COMPOSE_FILE" run --rm --no-deps nodejs pnpm run prod
