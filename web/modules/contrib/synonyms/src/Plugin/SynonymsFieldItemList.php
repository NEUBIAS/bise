<?php

namespace Drupal\synonyms\Plugin;

use Drupal\Core\Field\FieldItemList;
use Drupal\synonyms\SynonymsProviderInterface\SynonymsGetProviderInterface;

/**
 * Field item list of "synonyms" computed base field.
 */
class SynonymsFieldItemList extends FieldItemList {

  /**
   * {@inheritdoc}
   */
  public function getValue($include_computed = FALSE) {
    $synonyms = [];

    $entity = $this->getEntity();

    $behavior_service = \Drupal::getContainer()->get('synonyms.behaviors');

    $services = $behavior_service->getBehaviorServicesWithInterface(SynonymsGetProviderInterface::class);
    foreach ($services as $service_id => $service) {
      foreach ($behavior_service->getSynonymConfigEntities($service_id, $entity->getEntityTypeId(), $entity->bundle()) as $synonym_config) {
        $synonyms = array_merge($synonyms, $synonym_config->getProviderPluginInstance()->getSynonyms($entity, $synonym_config->getBehaviorConfiguration()));
      }
    }

    return array_unique($synonyms);
  }

}
