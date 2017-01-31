.PHONY: all build config

all: build config

build:
	composer install
	cd assets && npm install && npm run build
	cd assets && bower install

config:
	cp docs/ConfigDevelop.sample.php ConfigDevelop.php
