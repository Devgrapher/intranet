.PHONY: all help build composer composer-dev deploy-db rollback-db run-docker

all: build composer ## Default task to do build.

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(lastword $(MAKEFILE_LIST)) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

build: ## Build web-front.
	cd assets && npm install && npm run build
	cd assets && bower install --allow-root

composer: ## Install composer packages without dev tools.
	composer install --no-dev --optimize-autoloader

composer-dev: ## Install all composer packages.
	composer update

build-docker: build composer ## Build a Docker image. (ridibooks/intranet)
	docker build -t ridibooks/intranet:latest .

deploy-db: ## Migrate DB with Phinx
ifndef env
	$(eval env := $(shell read -p "Enter Phinx environment: " REPLY; echo $$REPLY))
endif
	vendor/bin/phinx migrate -e $(env)

rollback-db: ## Rollback DB with Phinx
ifndef env
	$(eval env := $(shell read -p "Enter Phinx environment: " REPLY; echo $$REPLY))
endif
	vendor/bin/phinx rollback -e $(env)

run-docker: ## Run web app with Docker.
	docker run -d --name ridi-intranet -p 8000:80 -v `pwd`:/var/www/html --env-file .env ridibooks/intranet:latest

sample-db:
	docker run --name mariadb -p 3306:3306 -e MYSQL_ALLOW_EMPTY_PASSWORD=1 -d mariadb:latest
	sleep 10s # Wait DB loading.
	composer install
	cp .env.sample .env
	mysql -uroot -h 127.0.0.1 -e "create database intranet;"
	vendor/bin/phinx migrate -e local -v
	vendor/bin/phinx seed:run -s Users -s Teams -s Rooms -s Policies -s Recipients -e local -v
