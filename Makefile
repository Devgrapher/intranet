.PHONY: all composer build config

all: build composer config

composer:
	composer install

build:
	cd assets && npm install && npm run build
	cd assets && bower install

config:
	cp docs/config.sample.env .env
