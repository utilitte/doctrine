.PHONY: phpstan tests cs csfix lint

check: tests cs phpstan lint

vendor: composer.json composer.lock
	composer install

tests: vendor
	vendor/bin/tester -s -p php --colors 1 -C tests/cases

lint: vendor
	vendor/bin/linter src tests

cs: vendor
	vendor/bin/codesniffer src tests

csfix: vendor
	vendor/bin/codefixer src tests

phpstan: vendor
	vendor/bin/phpstan analyse -l max -c phpstan.neon src
