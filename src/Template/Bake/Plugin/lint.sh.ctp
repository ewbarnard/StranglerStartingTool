#!/usr/bin/env bash -v
./vendor/bin/phpcs -p --extensions=php --standard=vendor/cakephp/cakephp-codesniffer/CakePHP ./src ./tests ./config ./webroot
./vendor/bin/phpunit
