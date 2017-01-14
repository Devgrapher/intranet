build:
	composer install
	cd assets && bower install
	cp docs/ConfigDevelop.sample.php ConfigDevelop.php
