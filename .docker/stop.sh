#!/bin/bash
set -e

source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/scripts/lib.sh"

echo -e "${RED}Stop all docker containers${NC}"
docker compose -f "$COMPOSE_FILE" down
