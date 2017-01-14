build:
	composer install
	cd assets && bower install
	cp docs/ConfigDevelop.php.sample ConfigDevelop.php
