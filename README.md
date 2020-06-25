clone this repo
$ cp ./puphpet/config.yaml.dist /puphpet/config.yaml
vagrant up
vagrant ssh
You will need to install a php extension Mysql_xdevapi https://www.php.net/manual/en/mysql-xdevapi.installation.php
cd /var/www

<!-- Install composer 1.7.1
cd ~/
https://getcomposer.org/download/ (follow 1st 2 steps)
cd /var/www
$ php ~/composer -->

composer install
yarn install
./bin/console doctrine:schema:create
add to your /etc/hosts file

192.168.56.109 pintex.test
192.168.56.109 www.pintex.test

Run Vagrant Install
./vendor/bin/phing vagrant-install

That's it!

Visit www.pintex.test in your browser

to compile your assets

vagrant ssh and cd into /var/www and run ./node_modules/.bin/encore dev

You also need to install php-amqp

You need to install Rabbit MQ:
https://tecadmin.net/install-rabbitmq-server-on-ubuntu/

sudo service rabbitmq-server reload

sudo apt-get update
sudo apt-get install php7.2-gd
