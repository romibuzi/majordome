HOST=127.0.0.1
PORT=8080

ifdef AWS_ACCESS_KEY_ID
AWS_ACCESS_ARG=-e AWS_ACCESS_KEY_ID=$(AWS_ACCESS_KEY_ID)
endif

ifdef AWS_SECRET_ACCESS_KEY
AWS_SECRET_ARG=-e AWS_SECRET_ACCESS_KEY=$(AWS_SECRET_ACCESS_KEY)
endif

help:
	@echo "    clean"
	@echo "        Clean cache and vendor dependencies."
	@echo "    install"
	@echo "        Install dependencies via composer."
	@echo "    install-dev"
	@echo "        Install dependencies (including dev) via composer."
	@echo "    test"
	@echo "        Run tests."
	@echo "    run"
	@echo "        Run Majordome process from command line."
	@echo "    run-web"
	@echo "        Run Majordome web interface through PHP built-in server."
	@echo "    docker-build"
	@echo "        Build the Majordome docker image."
	@echo "    docker-run"
	@echo "        Run Majordome process through docker."
	@echo "    docker-run-web"
	@echo "        Run Majordome web interface through docker."

clean:
	rm -rf var/cache var/log vendor

install:
	APP_ENV=prod composer install --no-dev

install-dev:
	APP_ENV=dev composer install

install-db:
	php bin/console doctrine:database:create --no-interaction
	php bin/console doctrine:migrations:migrate --no-interaction

test: install-dev
	bin/phpcs
	bin/phpunit

run:
	bin/console majordome:run-aws

run-web:
	php -S $(HOST):$(PORT) -t public/

docker-build:
	docker build --rm -t majordome:latest .
	if [ ! -f var/majordome_dev.db ]; then \
	mkdir -p var/; \
	docker create --name majordome-db majordome:latest; \
	docker cp majordome-db:/opt/majordome/var/majordome_dev.db var/majordome_dev.db; \
	docker rm -f majordome-db; \
fi

docker-run:
	docker run --rm \
	$(AWS_ACCESS_ARG) $(AWS_SECRET_ARG) \
	--mount type=bind,source=${PWD}/.env,target=/opt/majordome/.env \
	-v ${PWD}/var/:/opt/majordome/var/ \
	-v ${HOME}/.aws:/root/.aws:ro \
	-it --entrypoint bin/console \
	majordome:latest majordome:run-aws

docker-run-web:
	docker run --rm \
	--mount type=bind,source=${PWD}/.env,target=/opt/majordome/.env \
	-v ${PWD}/var/:/opt/majordome/var/ \
	-p 8080:8080 majordome:latest
