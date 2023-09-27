install:
	composer install;
validate:
	composer validate;
lint:
	composer exec --verbose phpcs -- --standard=PSR12 src bin
	composer exec --verbose phpcs -- --standard=PSR12 src tests
lint-fix:
	composer exec --verbose phpcbf -- --standard=PSR12 src bin
	composer exec --verbose phpcbf -- --standard=PSR12 src tests
test:
	composer exec --verbose phpunit tests
test-coverage:
	XDEBUG_MODE=coverage composer exec --verbose phpunit tests -- --coverage-clover build/logs/clover.xml