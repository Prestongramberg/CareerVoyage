1. clone this repo
2. vagrant up
3. vagrant ssh
4. cd /var/www
5. composer install
6. yarn install
7. ./bin/console doctrine:migrations:migrate
8. add to your /etc/hosts file

192.168.56.109 pintex.test
192.168.56.109 www.pintex.test

9. bin/console doctrine:fixtures:load --append

That's it!

Visit www.pintex.test in your browser

to compile your assets 

vagrant ssh and cd into /var/www and run ./node_modules/.bin/encore dev


