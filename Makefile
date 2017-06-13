.PHONY: all composer build config deploy deploy-db

all: build composer config

composer:
	composer install

build:
	cd assets && npm install && npm run build
	cd assets && bower install

config:
	cp docs/config.sample.env .env

deploy:
	vendor/bin/dep --file=docs/deployer/deploy.php deploy $$stage -p

deploy-db:
	vendor/bin/phinx migrate -e $$env
