# ET Building Project

The data collection server for the energy monitoring system developed by Department of Physics
at Naresuan University, together with [Mobile Computing Lab](http://www.mobcomlab.com).

## Requirements

* VirtualBox - Free virtualization software [Download Virtualbox](https://www.virtualbox.org/wiki/Downloads)
* Vagrant **1.3+** - Tool for working with virtualbox images [Download Vagrant](https://www.vagrantup.com)
* Git - Source Control Management [Download Git](http://git-scm.com/downloads)

## Setup

* Clone this repository `git clone http://github.com/mobcomlab/etbuilding.git`
* run `vagrant up` inside the newly created directory
* (the first time you run vagrant it will need to fetch the virtual box image which is ~300mb so depending on your download speed this could take some time)
* Vagrant will then use puppet to provision the base virtual box with our LAMP stack (this could take a few minutes) also note that composer will need to fetch all of the packages defined in the app's composer.json which will add some more time to the first provisioning run
* You can verify that everything was successful by opening http://localhost:8888 in a browser

*Note: You may have to change permissions on the www/app/storage folder to 777 under the host OS* 

For example: `chmod -R 777 www/app/storage/`

## Usage

Some basic information on interacting with the vagrant box

### Port Forwards

* 8888 - Apache
* 8889 - MySQL 


### Default MySQL Database

* User: root
* Password: (blank)
* DB Name: database

### PHPmyAdmin

Accessible at http://localhost:8888/phpmyadmin using MySQL access credentials above.

### Vagrant

Vagrant is [very well documented](http://vagrantup.com/v1/docs/index.html) but here are a few common commands:

* `vagrant up` starts the virtual machine and provisions it
* `vagrant suspend` will essentially put the machine to 'sleep' with `vagrant resume` waking it back up
* `vagrant halt` attempts a graceful shutdown of the machine and will need to be brought back with `vagrant up`
* `vagrant ssh` gives you shell access to the virtual machine

### AWS deployment

Easy deployment to the cloud (AWS), via `vagrant up --provider=aws`. The Vagrantfile contains configuration options for setting your keypair name, the the location of your key and the name of your server.


----
##### Virtual Machine Specifications #####

* OS     - Ubuntu 12.04
* Apache - 2.4.6
* PHP    - 5.5.4
* MySQL  - 5.5.32
* Beanstalkd - 1.4.6
* Redis - 2.2.12
* Memcached - 1.4.13
