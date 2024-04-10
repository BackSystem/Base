dc := docker compose
dr := $(dc) run --rm
php := $(dr) php

.PHONY: stan
stan:
	$(php) vendor/bin/phpstan analyse --memory-limit=-1

.PHONY: format
format:
	$(php) vendor/bin/php-cs-fixer fix --diff

.PHONY: lint
lint:
	make format
	make stan
