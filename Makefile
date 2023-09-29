test:
	./vendor/bin/phpunit tests/ --coverage-clover phpunit.coverage.xml --log-junit phpunit.report.xml

lint:
	./vendor/bin/phpcs
	./vendor/bin/phpstan analyse -c tests/phpstan.neon

format:
	./vendor/bin/phpcbf
