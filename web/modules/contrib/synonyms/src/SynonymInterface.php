<?php

namespace Drupal\synonyms;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\synonyms\SynonymsProviderInterface\SynonymsProviderInterface;

/**
 * Interface of synonyms configuration entity.
 */
interface SynonymInterface extends ConfigEntityInterface, EntityWithPluginCollectionInterface {

  /**
   * Get ID of the synonyms provider plugin that is set up in this entity.
   *
   * @return string
   *   Plugin ID of synonyms provider that corresponds to this configuration
   *   entity
   */
  public function getProviderPlugin();

  /**
   * Get instance of the synonyms provider plugin that is set up in this entity.
   *
   * @return SynonymsProviderInterface
   *   Initiated synonyms provider instance that corresponds to this
   *   configuration entity
   */
  public function getProviderPluginInstance();

  /**
   * Set the synonyms provider plugin to use in this entity.
   *
   * @param string $plugin
   *   Synonyms provider plugin ID to set in this configuration entity
   */
  public function setProviderPlugin($plugin);

  /**
   * Get synonyms provider plugin configuration from this entity.
   *
   * @return array
   *   Array of synonyms provider plugin configuration
   */
  public function getProviderConfiguration();

  /**
   * Set synonyms provider plugin configuration for this entity.
   *
   * @param array $provider_configuration
   *   Array of synonyms provider plugin configuration to set
   */
  public function setProviderConfiguration(array $provider_configuration);

  /**
   * Get synonyms behavior configuration from this entity.
   *
   * @return array
   *   Array of synonyms behavior configuration
   */
  public function getBehaviorConfiguration();

  /**
   * Set synonyms behavior configuration for this entity.
   *
   * @param array $behavior_configuration
   *   Array of synonyms behavior configuration to set
   */
  public function setBehaviorConfiguration(array $behavior_configuration);

}
