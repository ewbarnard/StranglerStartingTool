#!/usr/bin/env bash -v
./vendor/bin/phpcbf -p --extensions=php --standard=vendor/cakephp/cakephp-codesniffer/CakePHP ./src/Console/Installer.php
./vendor/bin/phpcbf -p --extensions=php --standard=vendor/cakephp/cakephp-codesniffer/CakePHP ./src/Shell/ConsoleShell.php
./vendor/bin/phpcs -p --extensions=php --standard=vendor/cakephp/cakephp-codesniffer/CakePHP ./src ./tests ./config ./webroot
./vendor/bin/phpunit
