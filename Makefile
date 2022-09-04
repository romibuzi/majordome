HOST=127.0.0.1
PORT=8080

help:
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

install:
	composer install --no-dev

install-dev:
	composer install

test:
	bin/phpcs
	bin/phpunit

run:
	app/console majordome:run-aws

run-web:
	php -S $(HOST):$(PORT) -t web/

docker-build:
	docker build --rm -t majordome:latest .

ifdef AWS_ACCESS_KEY_ID
AWS_ACCESS_ARG=-e AWS_ACCESS_KEY_ID=$(AWS_ACCESS_KEY_ID)
endif

ifdef AWS_SECRET_ACCESS_KEY
AWS_SECRET_ARG=-e AWS_SECRET_ACCESS_KEY=$(AWS_SECRET_ACCESS_KEY)
endif

docker-run:
	docker run --rm \
	$(AWS_ACCESS_ARG) $(AWS_SECRET_ARG) \
	--mount type=bind,source=${PWD}/app/config.php,target=/opt/majordome/app/config.php \
	-v ${PWD}/var/:/opt/majordome/var/ \
	-v ${HOME}/.aws:/root/.aws:ro \
	-it --entrypoint app/console \
	majordome:latest majordome:run-aws

docker-run-web:
	docker run --rm \
	--mount type=bind,source=${PWD}/app/config.php,target=/opt/majordome/app/config.php \
	-v ${PWD}/var/:/opt/majordome/var/ \
	-p 8080:8080 majordome:latest
