<?php

namespace Drupal\synonyms\SynonymsProviderInterface;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Trait to extract synonyms from an entity.
 */
trait SynonymsGetTrait {

  /**
   * Fetch synonyms from multiple entities at once.
   *
   * @param ContentEntityInterface[] $entities
   *   Array of entities whose synonyms should be fetched. They array will be
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
  public function getSynonymsMultiple(array $entities, array $behavior_configuration = []) {
    $synonyms = [];

    foreach ($entities as $entity_id => $entity) {
      $synonyms[$entity_id] = $this->getSynonyms($entity, $behavior_configuration);
    }

    return $synonyms;
  }

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
  abstract public function getSynonyms(ContentEntityInterface $entity, array $behavior_configuration = []);

}
