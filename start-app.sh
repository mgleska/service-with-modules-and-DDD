#!/bin/sh

set -ex

cd /app

if [ "$1" == "dev" ]
then
  composer install
fi

docker/wait-for database:3306 -t 120
sleep 2

php bin/console doctrine:database:create --if-not-exists | tee /tmp/database-creation-result
php bin/console doctrine:migrations:migrate --no-interaction
grep -q 'Created database' /tmp/database-creation-result && php bin/console admin:init-common
php bin/console admin:migrate-tenants
grep -q 'Created database' /tmp/database-creation-result && php bin/console admin:init-tenants

if [ "$1" == "dev" ]
then
  composer run-script apidoc &
fi

php -S 0.0.0.0:8000 -t public/
