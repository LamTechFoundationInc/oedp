#!/bin/bash
backuplocation=/usr/share/nginx/backups/electiondata.io/db
dev=/usr/share/nginx/dev.electiondata.io/docroot/sites/default
prod=/usr/share/nginx/prod.electiondata.io/docroot/sites/default
timestamp=$(date +%s)
vhost=/usr/share/nginx
cd $prod
drush status
drush cr
sudo drush cron
drush cex -y
drush sql-dump --result-file=$backuplocation/$timestamp.sql
git status
git add --all
git commit -am"$timestamp: captured latest configuration and backup database using script"

cd $vhost
mv dev.electiondata.io dev.electiondata.io.original
cp -R prod.electiondata.io/ dev.electiondata.io/
mv $dev/settings.php $dev/settings.php.prod
#mv $dev/settings.php $dev/settings.php.prod
cp dev.electiondata.io.original/docroot/sites/default/settings.php $dev/settings.php

cd $dev
drush status
drush sql-drop -y
drush sql-cli <$backuplocation/$timestamp.sql
drush status
drush cr
sudo drush cron
