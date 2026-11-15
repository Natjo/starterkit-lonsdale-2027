.PHONY: init start start-build stop composer sonar crawl prod

COMPOSE_FILE := .docker/docker-compose.yml

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
	COMPOSE_FILE="$(COMPOSE_FILE)" bash .docker/scripts/node-assets-prod.sh

%:
	@:

.PHONY: design-system design-system-watch design-system-export design-system-publish
design-system:
	$(MAKE) -C design-system docs

design-system-watch:
	$(MAKE) -C design-system watch

design-system-export:
	$(MAKE) -C design-system export URL="$(URL)"

design-system-publish:
	$(MAKE) -C design-system publish URL="$(URL)"
