<?php

namespace Drupal\synonyms\SynonymsProviderInterface;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\synonyms\SynonymsBehavior\SynonymsBehaviorInterface;

/**
 * Interface to extract (get) synonyms from an entity.
 */
interface SynonymsGetProviderInterface extends SynonymsProviderInterface {

  /**
   * Fetch synonyms from an entity.
   *
   * @param ContentEntityInterface $entity
   *   Entity whose synonyms should be fetched
   * @param array $behavior_configuration
   *   Configuration of the synonyms behavior that this provider should obey to
   *
   * @return string[]
   *   Array of extracted synonyms
   */
  public function getSynonyms(ContentEntityInterface $entity, array $behavior_configuration = []);

  /**
   * Fetch synonyms from multiple entities at once.
   *
   * @param ContentEntityInterface[] $entities
   *   Array of entities whose synonyms should be fetched. The array will be
   *   keyed by entity ID and all provided entities will be of the same entity
   *   type and bundle
   * @param array $behavior_configuration
   *   Configuration of the synonyms behavior that this provider should obey to
   *
   * @return array
   *   Array of extracted synonyms. It must be keyed by entity ID and each sub
   *   array should represent a list of synonyms that were extracted from the
   *   corresponding entity
   */
  public function getSynonymsMultiple(array $entities, array $behavior_configuration = []);

}
