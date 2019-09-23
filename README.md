clone this repo
$ cp ./puphpet/config.yaml.dist /puphpet/config.yaml
vagrant up
vagrant ssh
You will need to install a php extension Mysql_xdevapi https://www.php.net/manual/en/mysql-xdevapi.installation.php
cd /var/www
composer install
yarn install
./bin/console doctrine:schema:create
add to your /etc/hosts file

192.168.56.109 pintex.test
192.168.56.109 www.pintex.test

bin/console doctrine:fixtures:load --append

(To keep the tables starting at 1 index you need to completely delete all tables and rerun )

import the industries
./bin/console industry:import ./secondaryIndustries.csv

import the lessons
./bin/console lesson:import ./lessons.csv

import the schools
./bin/console school:import ./southeast-school-list.csv


That's it!

Visit www.pintex.test in your browser

to compile your assets 

vagrant ssh and cd into /var/www and run ./node_modules/.bin/encore dev

You also need to install php-amqp

You need to install Rabbit MQ: 
https://tecadmin.net/install-rabbitmq-server-on-ubuntu/

sudo service rabbitmq-server reload