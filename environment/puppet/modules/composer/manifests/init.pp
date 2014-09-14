#
# [*composer_dir*]
#   the directory where the composer.phar is stored
#
# [*vendor_dir*]
#   the directory where the composer.json is stored
#
#
class composer (
    $composer_dir = '/usr/local/bin',
    $vendor_dir = '/vagrant/vendor'
  ) {

  exec { 'download_composer':
    command   => "curl -s http://getcomposer.org/installer | php",
    cwd       => $composer_dir,
    creates   => "$composer_dir/composer.phar",
  }

  exec { 'composer-run':
    command => "php composer.phar install --working-dir $vendor_dir",
  cwd     => $composer_dir,
    environment => ["COMPOSER_HOME=$composer_dir"],
    user    => root,
    group   => root,
    timeout => 0,
    require => [Exec['download_composer']],
    logoutput => on_failure,
  }

}