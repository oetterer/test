#!/bin/bash
set -ex

originalDirectory=$(pwd)
cd ..
baseDir=$(pwd)
mwDir=mw


## Use sha (master@5cc1f1d) to download a particular commit to avoid breakages
## introduced by MediaWiki core
if [[ "${MW}" == *@* ]]
then
  arrMw=(${MW//@/ })
  MW=${arrMw[0]}
  SOURCE=${arrMw[1]}
else
 MW=${MW}
 SOURCE=${MW}
fi

function installMWCoreAndDB {

 echo "Installing MW Version ${MW}"

 cd ${baseDir}
 wget https://github.com/wikimedia/mediawiki/archive/${SOURCE}.tar.gz -O ${MW}.tar.gz
 tar -zxf ${MW}.tar.gz
 mv mediawiki-* ${mwDir}

 cd ${mwDir}

 composer self-update
 composer install --prefer-source

 echo "installing database ${DB}"

 if [[ "${DB}" == "postgres" ]]
 then
  sudo /etc/init.d/postgresql stop
  sudo /etc/init.d/postgresql start

  psql -c 'create database its_a_mw;' -U postgres
  php maintenance/install.php --dbtype ${DB} --dbuser postgres --dbname its_a_mw --pass nyan --scriptpath /TravisWiki TravisWiki admin
 else
  mysql -e 'create database its_a_mw;'
  php maintenance/install.php --dbtype ${DB} --dbuser root --dbname its_a_mw --dbpath $(pwd) --pass nyan --scriptpath /TravisWiki TravisWiki admin
 fi
}

function installSourceViaComposer {
 echo "missing"
}

function installSourceFromPull {
 echo "Installing Extension"
 cd ${baseDir}
 cd ${mwDir}

 composer require 'mediawiki/bootstrap=*' --update-with-dependencies

 cd extensions

 cp -r ${originalDirectory} .

 cd ..
 echo 'wfLoadExtension( "BootstrapComponents" );' >> LocalSettings.php
}

function augmentConfiguration {

 cd ${baseDir}
 cd ${mwDir}

 # Site language
 if [[ "${SITELANG}" != "" ]]
 then
  echo '$wgLanguageCode = "'${SITELANG}'";' >> LocalSettings.php
 fi

 echo 'error_reporting(E_ALL| E_STRICT);' >> LocalSettings.php
 echo 'ini_set("display_errors", 1);' >> LocalSettings.php
 echo '$wgShowExceptionDetails = true;' >> LocalSettings.php
 echo '$wgDevelopmentWarnings = true;' >> LocalSettings.php
 echo "putenv( 'MW_INSTALL_PATH=$(pwd)' );" >> LocalSettings.php

 php maintenance/update.php --quick --skip-external-dependencies
}

installMWCoreAndDB
installSourceFromPull
augmentConfiguration