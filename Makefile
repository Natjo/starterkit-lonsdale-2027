.PHONY: init start start-build stop composer sonar crawl prod

COMPOSE_FILE := .docker/docker-compose.yml
APP_NAME := $(shell grep '^PROJECT_NAME=' .docker/.env 2>/dev/null | cut -d= -f2- | tr -d '\r' || echo "starterkit-v2")
NODE_CONTAINER := $(APP_NAME)-node

init:
	.docker/init.sh $(filter-out $@,$(MAKECMDGOALS))

run:
	.docker/run.sh $(if $(filter build,$(MAKECMDGOALS)),--build)

stop:
	.docker/stop.sh

composer:
	docker compose -f $(COMPOSE_FILE) run --rm composer $(filter-out $@,$(MAKECMDGOALS))

sonar:
	docker compose -f $(COMPOSE_FILE) --profile sonar up -d

crawl:
	docker compose -f $(COMPOSE_FILE) --profile crawl up -d
	sleep 1
	open http://127.0.0.1:5050

prod:
	@docker inspect -f '{{.State.Running}}' $(NODE_CONTAINER) 2>/dev/null | grep -q true \
		|| docker compose -f $(COMPOSE_FILE) up -d nodejs
	docker exec -it $(NODE_CONTAINER) pnpm run prod

%:
	@:
