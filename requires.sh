#!/usr/bin/env bash
composer require 'cakephp/plugin-installer:^0.0'
composer require --dev 'cakephp/cakephp-codesniffer=2.*'
composer require --dev 'phpunit/phpunit:^5.4'
composer require --dev 'phpmd/phpmd:@stable'
composer require --dev 'mockery/mockery:^0.9'
composer dump-autoload --optimize
