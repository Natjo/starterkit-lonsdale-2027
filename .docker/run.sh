#!/bin/bash
set -e

source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/scripts/lib.sh"
load_env

BUILD_FLAG=""
[ "$1" = "--build" ] && BUILD_FLAG="--build"

echo -e "${GREEN}Build project docker container${NC}"
docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" up $BUILD_FLAG

echo -e "${GREEN}Website up and running:${NC}"
echo -e "${GREEN}${WP_HOME}${NC}"
echo -e "${GREEN}http://localhost:3000/${NC}"
