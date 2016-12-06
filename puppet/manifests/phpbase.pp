# Enable XDebug ("0" | "1")
$use_xdebug = "0"

# Default path
Exec 
{
  path => ["/usr/bin", "/bin", "/usr/sbin", "/sbin", "/usr/local/bin", "/usr/local/sbin"]
}

exec 
{ 
    'apt-get update':
        command => '/usr/bin/apt-get update',
        timeout => 900
}

include bootstrap
include other
include php
include apache
include mysql
include phpmyadmin
#include beanstalkd
#include redis
#include memcached
include composer

include laravel_app

