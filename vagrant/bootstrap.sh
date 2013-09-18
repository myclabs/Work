#!/bin/bash

apt-get update

# For PHP 5.5
apt-get install -y python-software-properties
add-apt-repository -y ppa:ondrej/php5

# RabbitMQ
echo "deb http://www.rabbitmq.com/debian/ testing main" > /etc/apt/sources.list.d/rabbitmq.list
wget http://www.rabbitmq.com/rabbitmq-signing-key-public.asc
apt-key add rabbitmq-signing-key-public.asc

apt-get update

apt-get install -y curl git php5-cli php-pear php5-xdebug rabbitmq-server

# Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# PHPUnit
pear config-set auto_discover 1
pear install pear.phpunit.de/PHPUnit
