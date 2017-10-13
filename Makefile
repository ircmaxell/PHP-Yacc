
build: build-examples
		php-cs-fixer fix ./lib

test:
		vendor/bin/phpunit --coverage-text

build-examples:
		php examples/rebuild.php

analyze:
		vendor/bin/phpstan analyze lib