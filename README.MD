##Project Setup 

    1. git clone git@github.com:joscraw/pintex.git
    
    2. Copy dist.env.local to .env.local
    
    3. vagrant plugin install vagrant-disksize 

    3. vagrant up

    4. vagrant ssh
    
    5. cd /var/www

    6. rm -R /var/www/var/cache
       rm -R /var/www/var/log
    
    6. php -d memory_limit=-1 /usr/bin/composer install
    
    7. yarn install
    
    8. yarn dev
    
    9. ./bin/console doctrine:schema:update --force
    
    10. Ask Eric or Josh how to get a copy of the staging or production database to import (see notes below on how to connect to the local database)
    


When you write some CSS/Javascript and need to re-compile:

vagrant ssh
cd /var/www
yarn dev

##To connect to the database:

Mysql 

Host: 127.0.0.1
User: pintex
Pass: pintex
Database: pintex

SSH

Host: 192.168.56.109
User: vagrant
password: (Don't use a password and select SSH Key and point to $PROJECT_DIR/.vagrant/machines/default/virtualbox/private_key)

NOTE: If you still can't connect to the database from a sequel client and you're getting some weird host error,
then just clear out the old host from the ~/.ssh/known_hosts file and try to reconnect. 

##Visit the app in the browser!

1. http://localhost:8000/