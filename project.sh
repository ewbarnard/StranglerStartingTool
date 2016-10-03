#!/usr/bin/env bash
if [ $# -eq 0 ]
 then
  echo "No arguments supplied"
  exit
 fi
CWD=`pwd`
PRJ="../Projects"
cd ${PRJ}
composer self-update
echo Y | composer create-project --prefer-dist cakephp/app $1
cd $1
composer require 'cakephp/plugin-installer:^0.0'
composer require --dev 'cakephp/cakephp-codesniffer=2.*'
composer require --dev 'phpunit/phpunit:^5.4'
composer require --dev 'phpmd/phpmd:@stable'
composer require --dev 'mockery/mockery:^0.9'

composer config repositories.generate-token '{"type": "path", "url": "../../Packages/Plain/GenerateToken"}'
composer require 'inboxdollars/generate-token:^1.0@dev'

composer config repositories.bats-rabbitmq '{"type": "path", "url": "../../Packages/Plain/RabbitMQ"}'
composer require 'inboxdollars/bats-rabbitmq:^1.0@dev'

composer config repositories.bats-support '{"type": "path", "url": "../../Packages/CakePhp3/BatsSupport"}'
composer require 'inboxdollars/bats-support:^3.0@dev'

mkdir src/Shell/Task
cp '../../Packages/CakePhp3/BatsSupport/Pull/ModelTask.php' src/Shell/Task

composer dump-autoload --optimize

./bin/cake plugin load BatsSupport
