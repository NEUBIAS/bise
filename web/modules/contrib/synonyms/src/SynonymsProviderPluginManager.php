<?php

namespace Drupal\synonyms;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\synonyms\Annotation\SynonymsProvider;
use Drupal\synonyms\SynonymsProviderInterface\SynonymsProviderInterface;

/**
 * Plugin manager for Synonyms provider plugin type.
 */
class SynonymsProviderPluginManager extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/Synonyms/Provider',
      $namespaces,
      $module_handler,
      SynonymsProviderInterface::class,
      SynonymsProvider::class
    );
    $this->alterInfo('synonyms_provider_info');
    $this->setCacheBackend($cache_backend, 'synonyms_provider_info_plugins');
  }

}
