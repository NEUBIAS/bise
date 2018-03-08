<?php

namespace Drupal\synonyms\Plugin\Synonyms\Provider;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\synonyms\SynonymsProviderInterface\SynonymsProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Good starting point for a synonyms provider plugin.
 */
abstract class AbstractProvider extends PluginBase implements SynonymsProviderInterface, ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * @var ContainerInterface
   */
  protected $container;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ContainerInterface $container) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->container = $container;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getBehaviorService() {
    return $this->getPluginDefinition()['synonyms_behavior_service'];
  }

  /**
   * {@inheritdoc}
   */
  public function getBehaviorServiceInstance() {
    return $this->container->get($this->getPluginDefinition()['synonyms_behavior_service']);
  }

}
