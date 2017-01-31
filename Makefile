.PHONY: all composer build config

all: build config

composer:
	composer install

build:
	cd assets && npm install && npm run build
	cd assets && bower install

config:
	cp docs/ConfigDevelop.sample.php ConfigDevelop.php
