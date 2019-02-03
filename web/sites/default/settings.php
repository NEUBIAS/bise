<?php


$databases = array();
$config_directories = array();
$settings['hash_salt'] = 'Uw7QotIATSJeoKl231mycyRkOQhL5i26pu9D0p6ptqEnreyjUYt6pZLgFyXTt1ePg9rDSxy8mg';
$settings['update_free_access'] = FALSE;
$settings['file_private_path'] = 'sites/default/files/private';
$settings['container_yamls'][] = $app_root . '/' . $site_path . '/services.yml';
$settings['file_scan_ignore_directories'] = [
  'node_modules',
  'bower_components',
];
$settings['entity_update_batch_size'] = 50;
$config_directories['sync'] = '../config/sync';
$settings['install_profile'] = 'standard';

/** database settings
**/
$databases['default']['default'] = array (
  'database' => 'drupal8',
  'username' => 'drupal8',
  'password' => 'drupal8',
  'prefix' => '',
  'host' => 'database',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);

$settings['trusted_host_patterns'] = [
  '^www\.biii\.eu$',
  '^biii\.eu$',
  '^test\.biii\.eu$',
  '^localhost$',
  'bisescratch\.lndo\.site',
];

/**
 * features from default settings.local.php
 * */
$settings['container_yamls'][] = DRUPAL_ROOT . '/sites/development.services.yml';
$config['system.logging']['error_level'] = 'verbose';
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;
// for module devl
$settings['cache']['bins']['render'] = 'cache.backend.null';
$settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
$settings['skip_permissions_hardening'] = TRUE;