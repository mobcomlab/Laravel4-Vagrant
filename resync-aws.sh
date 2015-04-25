#!/bin/bash

sshhostname="myproject"

# If this is first time then add ssh config
ssh -q $sshhostname quit
if [ $? -eq 255 ]
then
	echo "\n\n" >> ~/.ssh/config
	vagrant ssh-config --host $sshhostname | grep -v "fog" >> ~/.ssh/config
fi

# Sync files
rsync --verbose --delete --archive -z --exclude .vagrant/ --exclude vendor --exclude storage --exclude .svn --exclude public/res -e ssh www/ $sshhostname:/var/www

# Run composer install
ssh $sshhostname "sudo chmod -R 777 /var/www/storage ; cd /var/www ; sudo composer install"

