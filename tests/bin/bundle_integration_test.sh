#!/bin/bash

cd workspace

echo "Cloning RoutingAutoBundle"
echo "-------------------------"
echo ""

if [ ! -e workspace ];
then
    mkdir workspace
fi

cd workspace

if [ -e RoutingAutoBundle ]; then
    cd RoutingAutoBundle
    git pull origin master
else
    git clone https://github.com/symfony-cmf/RoutingAutoBundle
    cd RoutingAutoBundle
fi


echo "Installing dependencies"
echo "-----------------------"
echo ""

export SYMFONY_VERSION=2.4.*

composer require symfony-cmf/RoutingAuto dev-master --no-update --verbose
composer require symfony/framework-bundle:${SYMFONY_VERSION} --no-update --verbose
composer install --dev --prefer-dist --verbose

if [[ $? != 0 ]]; then
    exit 'Failed to install deps'
    exit(127)
fi

echo "Initializing Jackalope Doctrine-DBAL"
echo "------------------------------------"
echo ""

sh vendor/symfony-cmf/testing/bin/travis/phpcr_odm_doctrine_dbal.sh

echo "Running tests"
echo "-------------"
echo ""

phpunit

cd ..
