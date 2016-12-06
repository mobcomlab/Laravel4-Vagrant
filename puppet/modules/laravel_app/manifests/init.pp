class laravel_app
{

	package { 'git-core':
    	ensure => present,
    }

	exec { 'install packages':
        command => "/bin/sh -c 'cd /var/www/ && composer install'",
        require => [Package['git-core'], Exec['global composer']],
        onlyif => [ "test -f /var/www/composer.json" ],
        creates => "/var/www/vendor/autoload.php",
        timeout => 900,
        logoutput => true
	}

	file { '/var/www/app/storage':
		mode => 0777
	}
}
