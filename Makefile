install:
	composer install;
validate:
	composer validate;
lint:
	composer exec --verbose phpcs -- --standard=PSR12 src bin
test-coverage:
	composer exec --verbose phpunit tests -- --coverage-clover build/logs/clover.xml