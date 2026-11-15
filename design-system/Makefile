.DEFAULT_GOAL := help
ROOT := $(abspath ..)

.PHONY: help install docs watch export publish test
help:
	@echo "make docs                     Générer la documentation"
	@echo "make watch                    Surveiller README et manifestes"
	@echo "make export URL=https://…     Générer dist/ sans publier"
	@echo "make publish                  Générer et publier sur la branche configurée"
	@echo "make test                     Vérifier routes, TLS et publication"

install:
	npm install --ignore-scripts

docs: install
	node render-docs.mjs "$(ROOT)" "$(CURDIR)/docs"

watch: docs
	node render-docs.mjs "$(ROOT)" "$(CURDIR)/docs" --watch

export: docs
	python3 export.py "$(URL)" "$(CURDIR)/dist"

publish: docs
	python3 publish.py --url "$(URL)"

test:
	python3 test-routes.py
	python3 test-local-tls.py
	python3 test-publish.py
