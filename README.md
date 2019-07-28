1. clone this repo
2. vagrant up
3. vagrant ssh
4. cd /var/www
5. composer install
6. yarn install
7. ./bin/console doctrine:schema:create
8. add to your /etc/hosts file

192.168.56.109 pintex.test
192.168.56.109 www.pintex.test

9. bin/console doctrine:fixtures:load --append

(To keep the tables starting at 1 index you need to completely delete all tables and rerun )

10. import the industries
./bin/console industry:import ./secondaryIndustries.csv

11. import the lessons
./bin/console lesson:import ./lessons.csv

12. import the schools
./bin/console school:import ./southeast-school-list.csv



That's it!

Visit www.pintex.test in your browser

to compile your assets 

vagrant ssh and cd into /var/www and run ./node_modules/.bin/encore dev


