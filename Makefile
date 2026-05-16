## MellogangVisuals — convenience commands.
## Run `make help` to list available targets.

SHELL := /bin/bash
PHP   ?= php
COMPOSE ?= docker compose

.DEFAULT_GOAL := help

.PHONY: help install serve migrate migrate-rollback seed fresh test \
        docker-build docker-up docker-down docker-logs docker-shell \
        docker-migrate docker-seed docker-fresh clean

help: ## Show this help
	@awk 'BEGIN {FS = ":.*## "} /^[a-zA-Z_-]+:.*## / {printf "  \033[36m%-18s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

## -- Local (host PHP) ---------------------------------------------------------

install: ## Install Composer dependencies
	composer install

serve: ## Start the built-in PHP dev server on :8080
	$(PHP) spark serve --port 8080

migrate: ## Run pending migrations
	$(PHP) spark migrate --all

migrate-rollback: ## Roll back the most recent migration batch
	$(PHP) spark migrate:rollback

seed: ## Run the DatabaseSeeder
	$(PHP) spark db:seed DatabaseSeeder

fresh: ## Roll back everything, re-migrate, then seed
	$(PHP) spark migrate:rollback -b 0
	$(PHP) spark migrate --all
	$(PHP) spark db:seed DatabaseSeeder

test: ## Run PHPUnit
	vendor/bin/phpunit

clean: ## Remove build artifacts and caches
	rm -rf build writable/cache/* writable/debugbar/* writable/logs/*.log

## -- Docker -------------------------------------------------------------------

docker-build: ## Build the app image
	$(COMPOSE) build

docker-up: ## Start the stack in the background
	$(COMPOSE) up -d

docker-down: ## Stop and remove the stack (keeps volumes)
	$(COMPOSE) down

docker-logs: ## Tail logs from the running stack
	$(COMPOSE) logs -f --tail=200

docker-shell: ## Open a shell in the app container
	$(COMPOSE) exec app bash

docker-migrate: ## Run migrations inside the app container
	$(COMPOSE) exec app php spark migrate --all

docker-seed: ## Run the DatabaseSeeder inside the app container
	$(COMPOSE) exec app php spark db:seed DatabaseSeeder

docker-fresh: ## Roll back, migrate, and seed inside the app container
	$(COMPOSE) exec app php spark migrate:rollback -b 0
	$(COMPOSE) exec app php spark migrate --all
	$(COMPOSE) exec app php spark db:seed DatabaseSeeder
