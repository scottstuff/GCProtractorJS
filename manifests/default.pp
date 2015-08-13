group { 'puppet': ensure => present }
Exec { path => [ '/bin/', '/sbin/', '/usr/bin/', '/usr/sbin/' ] }
File { owner => 0, group => 0, mode => 0644 }

class {'apt':
  always_apt_update => true,
}

Class['::apt::update'] -> Package <|
    title != 'python-software-properties'
and title != 'software-properties-common'
|>

    apt::key { '4F4EA0AAE5267A6C': }

apt::ppa { 'ppa:ondrej/php5':
  require => Apt::Key['4F4EA0AAE5267A6C']
}

class { 'puphpet::dotfiles': }

package { [
    'build-essential',
    'vim',
    'curl',
    'git-core',
    'nodejs',
    'npm',
    'git',
    'memcached'
  ]:
  ensure  => 'installed',
}

class { 'apache': }

apache::dotconf { 'custom':
  content => 'EnableSendfile Off',
}

apache::module { 'rewrite': }
apache::module { 'headers': }

apache::vhost { 'gotchosen.dev':
  server_name   => 'gotchosen.dev',
  serveraliases => [
],
  docroot       => '/var/www/web/',
  port          => '80',
  env_variables => [
    'APP_ENV dev'
  ],
  priority      => '1',
  directory_allow_override => 'All',
}

class { 'php':
  service             => 'apache',
  service_autorestart => false,
  module_prefix       => '',
}

php::module { 'php5-mysql': }
php::module { 'php5-cli': }
php::module { 'php5-curl': }
php::module { 'php5-intl': }
php::module { 'php5-mcrypt': }
php::module { 'php-apc': }
php::module { 'php5-memcached': }

class { 'php::devel':
  require => Class['php'],
}



class { 'composer':
  require => Package['php5', 'curl'],
}

class { 'mysql::server':
  config_hash   => { 'root_password' => 'gotchosen',
                     'bind_address' => '0.0.0.0'
 }
}

mysql::db { 'gotchosen':
  grant    => [
    'ALL'
  ],
  user     => 'gotchosen',
  password => 'gotchosen',
  host     => '%',
  charset  => 'utf8',
  require  => Class['mysql::server'],
}

database_user { 'gotchosen@localhost':
  password_hash => mysql_password('gotchosen')
}

database_grant { 'gotchosen@localhost/gotchosen':
  privileges => ['all'] ,
}
