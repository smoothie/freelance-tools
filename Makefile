PATH_QA_BIN=./tools/
PATH_QA_CONFIG=./
EXEC_PHP=/usr/bin/php
APP_ENV=dev
CURRENT_DIR=$(shell pwd)

.DEFAULT_GOAL:= help
.PHONY: help

help: ## Show this help.
	@echo "Available targets:"
	@echo "=================="
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}'

check-build-php: ## Check composer stuff (e.g. lock in sync).
	${PATH_QA_BIN}composer validate --ansi --strict
	${PATH_QA_BIN}composer-normalize composer.json --diff --dry-run --no-interaction --ansi
	${PATH_QA_BIN}composer check-platform-reqs --ansi
	${PATH_QA_BIN}composer audit --ansi --no-interaction

check-acceptance: ## Run acceptance test suite.
	XDEBUG_TRIGGER=PHPSTROM ${PATH_QA_BIN}behat --strict --xdebug

check-integration: ## Run integration test suite.
	XDEBUG_TRIGGER=PHPSTROM ${PATH_QA_BIN}phpunit --configuration="phpunit.xml.dist" --testsuite="Integration"

check-unit: ## Run unit test suite.
	XDEBUG_TRIGGER=PHPSTROM ${PATH_QA_BIN}phpunit --configuration="phpunit.xml.dist" --testsuite="Unit"

check-tests: check-unit check-integration check-acceptance ## Run the test suites

check-quality: check-build-php check-linters check-cs check-types check-layers check-tests ## Assure some kind of a quality.

check-linters: ## Run all the linters.
	${EXEC_PHP} ./bin/console lint:container --ansi --no-interaction
	${EXEC_PHP} ./bin/console lint:yaml config src --ansi --no-interaction

check-layers: ## Ensure application adheres to layers.
	${EXEC_PHP} ${PATH_QA_BIN}deptrac analyse --config-file=depfile.yaml --report-uncovered --fail-on-uncovered --no-progress --no-interaction --ansi

check-cs: ## Ensure application adheres to code style.
	${EXEC_PHP} ${PATH_QA_BIN}php-cs-fixer check --config=${PATH_QA_CONFIG}.php-cs-fixer.dist.php --no-interaction --ansi

check-types: ## Ensure static code analyzer is happy.
	${EXEC_PHP} ${PATH_QA_BIN}phpstan analyse --configuration=phpstan.neon.dist --no-progress --error-format=table

fix-cs: ## Fix code style.
	${EXEC_PHP} ${PATH_QA_BIN}php-cs-fixer fix --config=${PATH_QA_CONFIG}.php-cs-fixer.dist.php --no-interaction

fix-composer-normalize: ## Fix composer-normalize.
	${PATH_QA_BIN}composer-normalize composer.json --no-interaction --ansi

debug-env-vars:
	${EXEC_PHP} ./bin/console debug:container --env-vars --ansi --no-interaction

debug-container:
	${EXEC_PHP} ./bin/console debug:container --ansi --no-interaction

init:
	bin/console secrets:generate-keys
