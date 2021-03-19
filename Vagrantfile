# -*- mode: ruby -*-
# vi: set ft=ruby :

# Use this Vagrant configuration file for local installation of the Pintex application.
# Please, refer to the Pintex Applications installation guides for the detailed instructions:

# All Vagrant configuration is done below. The "2" in Vagrant.configure
# configures the configuration version (we support older styles for
# backwards compatibility). Please don't change it unless you know what
# you're doing.

Vagrant.configure("2") do |config|
  config.vm.box = "ubuntu/xenial64"
  config.disksize.size = '50GB'

  config.vm.network "forwarded_port", guest: 80, host: 8000

  config.vm.network "forwarded_port", guest: 8080, host: 8080

  config.vm.network "forwarded_port", guest: 8025, host: 8025

  config.vm.network "private_network", ip: "192.168.56.109"

  # On Windows we must check if the plugin vagrant-winnfsd if installed. This plugin must be installed to be able to use NFS
  if Vagrant::Util::Platform.windows? then
     unless Vagrant.has_plugin?("vagrant-winnfsd")
       raise  Vagrant::Errors::VagrantError.new, "vagrant-winnfsd plugin is missing. Please install it using 'vagrant plugin install vagrant-winnfsd' and rerun 'vagrant up'"
     end
  end

  config.vm.synced_folder ".", "/var/www", type: "nfs"

  config.vm.provider "virtualbox" do |vb|
    vb.customize ["setextradata", :id, "VBoxInternal2/SharedFoldersEnableSymlinksCreate/var/www", "1"]

    # Customize the amount of memory on the VM:
    vb.memory = 4096
    vb.cpus = 2
  end


  config.vm.provision "shell", inline: <<-SHELL


		echo "\n*****************************************************"
		echo "************* Provision process started *************"
		echo "*****************************************************\n"

		# --------------------- Provision configuration ---------------------

		# --- VM settings ---

		FORWARDED_PORT=8000

		# --- Database settings ---

		DB_USER="pintex"
		DB_PASSWORD="pintex"
		DB_NAME="pintex"

		# --- application settings ---

		APP_HOST="localhost"
		APP_USER="admin"
		APP_PASSWORD="adminpass"
		APP_LOAD_DEMO_DATA="y"		# y | n

		echo "\n*******************************************************"
		echo "************** Step 1: Environment Setup **************"
		echo "*******************************************************\n"




		echo "\n~~~~~~~~~~~~~~ Enable Required Package Repositories ~~~~~~~~~~~~~~\n"

        # Needed for PHP 7.3
        LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php
		apt-get update
		#apt-get upgrade -y


		echo "\n~~~~~~~~~~~~~~ Install Nginx, Git, Supervisor, and Wget ~~~~~~~~~~~~~~\n"

		apt-get install -y nginx wget lftp git supervisor zlib1g-dev libpng-dev libmagickwand-dev --no-install-recommends
        ufw allow 'Nginx HTTP'
        echo "y" | ufw enable



		echo "\n~~~~~~~~~~~~~~ Install MySQL ~~~~~~~~~~~~~~\n"

        export DEBIAN_FRONTEND=noninteractive
        export TZ="America/Chicago"
        MYSQL_ROOT_PASSWORD='root' # SET THIS! Avoid quotes/apostrophes in the password, but do use lowercase + uppercase + numbers + special chars
        apt-get install -y mysql-server

        mysql -u root <<-EOF
        UPDATE mysql.user SET authentication_string=PASSWORD('$MYSQL_ROOT_PASSWORD'), plugin="mysql_native_password" where User='root' and Host='localhost';
        DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
        DELETE FROM mysql.user WHERE User='';
        DELETE FROM mysql.db WHERE Db='test' OR Db='test_%';
        FLUSH PRIVILEGES;
EOF

        ufw enable
        ufw allow 22
        ufw allow 3306

        service mysql restart

        echo "\n~~~~~~~~~~~~~~ Prepare MySQL Database ~~~~~~~~~~~~~~\n"

        # --- Change the MySQL Server Configuration ---

        echo "[client]" >> /etc/my.cnf
        echo "default-character-set = utf8mb4" >> /etc/my.cnf
        echo "" >> /etc/my.cnf
        echo "[mysql]" >> /etc/my.cnf
        echo "default-character-set = utf8mb4" >> /etc/my.cnf
        echo "" >> /etc/my.cnf
        echo "[mysqld]" >> /etc/my.cnf
        echo "innodb_file_per_table = 0" >> /etc/my.cnf
        echo "wait_timeout = 28800" >> /etc/my.cnf
        echo "character-set-server = utf8mb4" >> /etc/my.cnf
        echo "collation-server = utf8mb4_unicode_ci" >> /etc/my.cnf

        service mysql restart

        echo "\n~~~~~~~~~~~~~~ Setup Database and User ~~~~~~~~~~~~~~\n"

        mysql -uroot -p$MYSQL_ROOT_PASSWORD -e "CREATE DATABASE $DB_NAME"
        mysql -uroot -p$MYSQL_ROOT_PASSWORD -e "GRANT ALL PRIVILEGES ON $DB_NAME.* to '$DB_USER'@'localhost' identified by '$DB_PASSWORD'"
        mysql -uroot -p$MYSQL_ROOT_PASSWORD -e "FLUSH PRIVILEGES"

        echo "MySQL setup completed. Insecure defaults are gone. Please remove this script manually when you are done with it (or at least remove the MySQL root password that you put inside it."


        echo "\n~~~~~~~~~~~~~~ Install PHP ~~~~~~~~~~~~~~\n"

        apt-get install -y php7.3-fpm php7.3-curl php7.3-mysql php7.3-cli php7.3-pdo php7.3-mysqlnd php7.3-xml php7.3-soap php7.3-gd php7.3-zip php7.3-intl php7.3-mbstring php7.3-opcache php7.3-imagick

        service php7.3-fpm restart


        echo "\n~~~~~~~~~~~~~~ Configure Web Server ~~~~~~~~~~~~~~\n"

        		cat > /etc/nginx/conf.d/default.conf <<____NGINXCONFIGTEMPLATE
        server {
        	server_name $APP_HOST www.$APP_HOST;
        	root  /var/www/public;

        	index index.php;

        	gzip on;
        	gzip_proxied any;
        	gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;
        	gzip_vary on;

        	location / {
        		# try to serve file directly, fallback to index.php
        		try_files \\$uri /index.php\\$is_args\\$args;
        	}

        	location ~ ^/(index|index_dev|config|install)\\.php(/|$) {
        		#fastcgi_pass 127.0.0.1:9000;
        		# or
        	    fastcgi_pass unix:/var/run/php/php7.3-fpm.sock;
        		fastcgi_split_path_info ^(.+\\.php)(/.*)$;
        		include fastcgi_params;
        		fastcgi_param SCRIPT_FILENAME \\$document_root\\$fastcgi_script_name;
        		fastcgi_param HTTPS off;
        		fastcgi_buffers 64 64k;
        		fastcgi_buffer_size 128k;
        	}

        	location ~* ^[^(\\.php)]+\\.(jpg|jpeg|gif|png|ico|css|pdf|ppt|txt|bmp|rtf|js)$ {
        		access_log off;
        		expires 1h;
        		add_header Cache-Control public;
        	}

        	error_log /var/log/nginx/${APP_HOST}_error.log;
        	access_log /var/log/nginx/${APP_HOST}_access.log;
        }
____NGINXCONFIGTEMPLATE

        service nginx restart

        echo "\n~~~~~~~~~~~~~~ Configure PHP ~~~~~~~~~~~~~~\n"

        sed -i 's/;catch_workers_output = yes/catch_workers_output = yes/g' /etc/php/7.3/fpm/pool.d/www.conf

        sed -i 's/memory_limit = [0-9MG]*/memory_limit = 1G/g' /etc/php/7.3/fpm/php.ini
        sed -i 's/;realpath_cache_size = [0-9MGk]*/realpath_cache_size = 4M/g' /etc/php/7.3/fpm/php.ini
        sed -i 's/;realpath_cache_ttl = [0-9]*/realpath_cache_ttl = 600/g' /etc/php/7.3/fpm/php.ini

        echo "\n~~~~~~~~~~~~~~ Configure Web Server ~~~~~~~~~~~~~~\n"

        		cat > /etc/php/7.3/fpm/conf.d/10-opcache.ini <<____OPCACHETEMPLATE

        ; configuration for php opcache module
        ; priority=10
        zend_extension=opcache.so

        opcache.enable=1
        opcache.enable_cli=0
        opcache.memory_consumption=512
        opcache.interned_strings_buffer=32
        opcache.max_accelerated_files=32531
        opcache.save_comments=1

____OPCACHETEMPLATE


        service php7.3-fpm restart


        echo "\n~~~~~~~~~~~~~~ Install Node ~~~~~~~~~~~~~~\n"

        curl -sL https://deb.nodesource.com/setup_12.x | sudo -E bash -
        apt-get install -y nodejs

        echo "\n~~~~~~~~~~~~~~ Install Yarn ~~~~~~~~~~~~~~\n"

        curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | sudo apt-key add -
        echo "deb https://dl.yarnpkg.com/debian/ stable main" | sudo tee /etc/apt/sources.list.d/yarn.list
        apt-get update && apt-get install -y yarn

        echo "\n~~~~~~~~~~~~~~ Install Composer ~~~~~~~~~~~~~~\n"

        php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && php composer-setup.php
        php -r "unlink('composer-setup.php');"
        mv composer.phar /usr/bin/composer

        echo "********************************************************************************"
        echo "************** Step 2: Pre-installation Environment Configuration **************"
        echo "********************************************************************************"


        su - vagrant -c 'composer install --optimize-autoloader -n --working-dir=/var/www'
        #composer install --prefer-dist

        #sed -i "s/database_user:[ ]*root/database_user: $DB_USER/g" ./config/parameters.yml
        #sed -i "s/database_password:[ ]*null/database_password: $DB_PASSWORD/g" ./config/parameters.yml
        #sed -i "s/database_name:[ ]*[a-zA-Z0-9_]*/database_name: $DB_NAME/g" ./config/parameters.yml


        #cat >> ./config/config.yml <<____DOCTRINECONFIG

#doctrine:
 #   dbal:
  #      charset: utf8mb4
   #     default_table_options:
    #        charset: utf8mb4
     #       collate: utf8mb4_unicode_ci

#____DOCTRINECONFIG

        echo "\n~~~~~~~~~~~~~~ Schedule Periodical Command Execution ~~~~~~~~~~~~~~\n"

        touch /etc/cron.d/pintex_cron
        echo "*/1 * * * * root php /var/www/bin/console swiftmailer:spool:send" > /etc/cron.d/pintex_cron

        echo "\n~~~~~~~~~~~~~~ Configure and Run Required Background Processes ~~~~~~~~~~~~~~\n"

        touch /etc/supervisor/conf.d/pintex.conf

        cat > /etc/supervisor/conf.d/pintex.conf <<____SUPERVISORDTEMPLATE

[program:pintex_message_consumer]
command=php ./bin/console messenger:consume async
process_name=%(program_name)s_%(process_num)02d
numprocs=5
autostart=true
autorestart=true
directory=/var/www
user=www-data
redirect_stderr=true
startretries=10
____SUPERVISORDTEMPLATE

        cat >> /etc/supervisor/supervisord.conf <<____SUPERVISORDSERVER
[inet_http_server]
port=9001
username=test
password=test
____SUPERVISORDSERVER

        ufw allow 9001
        ufw reload

        service supervisor restart


        echo "\n~~~~~~~~~~~~~~ Install Mailhog for email debugging/testing ~~~~~~~~~~~~~~\n"

        apt-get -y install golang-go
        mkdir ~/gocode
        echo "export GOPATH=$HOME/gocode" >> ~/.profile
        source ~/.profile

        go get github.com/mailhog/MailHog
        cp ~/gocode/bin/MailHog /usr/local/bin/mailhog

        cat >> /etc/systemd/system/mailhog.service <<____MAILHOGSERVICE
[Unit]
Description=MailHog service

[Service]
ExecStart=/usr/local/bin/mailhog \
  -api-bind-addr 192.168.56.109:8025 \
  -ui-bind-addr 192.168.56.109:8025 \
  -smtp-bind-addr 127.0.0.1:1025

[Install]
WantedBy=multi-user.target
____MAILHOGSERVICE

        systemctl start mailhog
        systemctl enable mailhog
        systemctl | grep mailhog

        ufw allow 8025

        cat >> /etc/mysql/mysql.conf.d/mysqld.cnf <<____MYSQLMYCNF

sql_mode = "STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION"

____MYSQLMYCNF

        service mysql restart


        echo "\n**********************************************************************************************************************"
        echo "************** Congratulations! You’ve Successfully Installed the Pintex Application **********************************"
        echo "**********************************************************************************************************************\n"
        echo "\n************** You should now be able to open the homepage http://$APP_HOST:$FORWARDED_PORT/ and use the application. **************\n"

   SHELL



end
