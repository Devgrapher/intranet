.PHONY: all composer build config deploy deploy-db

all: build composer

build:
	cd assets && npm install && npm run build
	cd assets && bower install --allow-root

composer:
	composer install --no-dev --optimize-autoloader

config:
	cp docs/config.sample.env .env

deploy:
ifndef stage
	$(eval stage := $(shell read -p "Enter deployer stage: " REPLY; echo $$REPLY))
endif
	vendor/bin/dep --file=docs/deployer/deploy.php deploy $(stage) -p

deploy-db:
ifndef env
	$(eval env := $(shell read -p "Enter phinx environment: " REPLY; echo $$REPLY))
endif
	vendor/bin/phinx migrate -e $(env)
