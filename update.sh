#!/bin/bash

# Fetch updates from Github
git pull upstream dev
lando start
lando composer install
lando drush cr 
lando drush cache-import
