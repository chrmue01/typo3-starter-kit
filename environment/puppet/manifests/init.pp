Exec { path => [ "/bin/", "/sbin/" , "/usr/bin/", "/usr/sbin/" ] }

exec { 'apt-get update':
  command => 'apt-get update',
}

$sysPackages = [ 'build-essential', 'git', 'curl', 'python-software-properties', 'imagemagick']
package { $sysPackages:
  ensure => "installed",
  require => Exec['apt-get update'],
}

class { 'apache':
    require => Exec['apt-get update'],
}

apache::module { 'rewrite': }

apache::vhost { 'app.loc':
  port    => '80',
  docroot => '/home/vagrant/htdocs'
}

class { 'php':
  service => 'apache',
  require => Package['apache'],
}

class { "mysql":
  root_password => 'root',
  require => Class['php'],
}

mysql::grant { 'typo3-skeleton':
  mysql_privileges => 'ALL',
  mysql_password => 'user',
  mysql_db => 'typo3-starter-kit',
  mysql_user => 'user',
  mysql_host => '127.0.0.1',
  mysql_db_init_query_file => '/home/vagrant/database/typo3-starter-kit.sql',
}

$phpModules = [ 'imagick', 'curl', 'mysql', 'cli', 'intl', 'mcrypt', 'memcache', 'gd' ]

php::module { $phpModules: }

php::module { "apc":
  module_prefix => "php-",
  require => Class['php'],
}

php::ini { 'php':
  template => 'php.ini',
  target  => 'php.ini',
  service => 'apache',
}

include pear
pear::package { "PHPUnit":
  version => "3.7.35",
  repository => "pear.phpunit.de",
  require => Class['php'],
}

class { 'composer':
  require   => Package['php'],
}