Docker Console :

docker exec -it jeuxolympiquesstudi-app-1 bash

Tailwind déploiement :

php bin/console tailwind:build --minify
php bin/console asset-map:compil

DB Creation :

php bin/console doctrine:database:create

Migration

php bin/console doctrine:migrations:migrate

Fixtures

php bin/console doctrine:fixtures:load