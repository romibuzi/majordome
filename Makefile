THIS_FILE=$(lastword $(MAKEFILE_LIST))

HOST=127.0.0.1
PORT=8080

install:
	composer install --no-dev
	@$(MAKE) -f $(THIS_FILE) install-db

install-dev:
	composer install
	@$(MAKE) -f $(THIS_FILE) install-db

install-db:
	test -f var/majordome.db || cat var/schema.sql | sqlite3 var/majordome.db

test:
	bin/phpcs
	bin/phpunit

run:
	app/console majordome:run-aws

run-web:
	php -S $(HOST):$(PORT) -t web/

help:
	@echo "    install"
	@echo "        Install depedencies via composer."
	@echo "    install-dev"
	@echo "        Install depedencies (with dev ones) via composer."
	@echo "    test"
	@echo "        Run tests."
	@echo "    run"
	@echo "        Run Majordome process from command line."
	@echo "    run-web"
	@echo "        Run Majordome web interface through PHP built-in server."
