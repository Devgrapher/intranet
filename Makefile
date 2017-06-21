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
ifndef stage
	$(eval stage := $(shell read -p "Enter deployer stage: " REPLY; echo $$REPLY))
endif
	vendor/bin/dep --file=docs/deployer/deploy.php deploy $(stage) -p


deploy-db:
ifndef env
	$(eval env := $(shell read -p "Enter phinx environment: " REPLY; echo $$REPLY))
endif
	vendor/bin/phinx migrate -e $(env)
