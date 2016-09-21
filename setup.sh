#! /bin/bash

# git submodule init
# git submodule update
cp config/ProjectConfiguration.class.php.dist config/ProjectConfiguration.class.php
cp config/databases.yml.dist config/databases.yml
# cp web/.htaccess.dist web/.htaccess
cp apps/frontend/config/app.yml.dist apps/frontend/config/app.yml
cp apps/frontend/config/settings.yml.dist apps/frontend/config/settings.yml
cp apps/api/config/app.yml.dist apps/api/config/app.yml
cp apps/api/config/settings.yml.dist apps/api/config/settings.yml
cp config/sphinx.conf.dist config/sphinx.conf
mkdir -p data/sfWebBrowserPlugin/sfCurlAdapter
touch data/sfWebBrowserPlugin/sfCurlAdapter/cookies.txt
mkdir tmp
chmod -R 777 tmp
mkdir -p log/sphinx
touch log/sphinx/searchd.log
touch log/sphinx/query.log
touch log/sphinx/searchd.pid
chmod a+w log/sphinx/*
