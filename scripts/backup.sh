#!/bin/bash
backuplocation=/usr/share/nginx/backup/electiondata.io/db
dev=/usr/share/nginx/dev.electiondata.io/docroot/sites/default
prod=/usr/share/nginx/prod.electiondata.io/docroot/sites/default
timestamp=$(date +%s)
cd ../docroot/sites/default
drush status
drush cr
drush cron
drush cex -y
drush sql-dump --result-file=backuplocation/timestamp.sql
git status
git add --all
git commit -am"captured latest configuration and backup database using script"

#cd /usr/share/nginx/
#mv dev.electiondata.io dev.electiondata.io.original
#cp -R prod.electiondata.io/ dev.electiondata.io/
#mv $dev/settings.php $dev/settings.php.prod
#mv $dev/settings.php $dev/settings.php.prod
#cp dev.electiondata.io.original/docroot/sites/default/settings.php $dev/settings.php

#cd $dev
#drush status
#drush cr
#drush cron
