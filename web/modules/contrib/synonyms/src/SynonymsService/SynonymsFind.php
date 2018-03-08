<?php

namespace Drupal\synonyms\SynonymsService;

use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\synonyms\SynonymsProviderInterface\SynonymsFindProviderInterface;

/**
 * Service that allows to look up entities by their synonyms.
 */
class SynonymsFind {

  /**
   * @var BehaviorService
   */
  protected $behaviorService;

  /**
   * @var EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * SynonymsFind constructor.
   */
  public function __construct(BehaviorService $behavior_service, EntityTypeBundleInfoInterface $entity_type_bundle_info) {
    $this->behaviorService = $behavior_service;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
  }

  /**
   * Lookup entity IDs by the $condition.
   *
   * @param Condition $condition
   *   Condition which defines what to search for
   * @param EntityTypeInterface $entity_type
   *   Entity type within which to search
   * @param string|array $bundle
   *   Either single bundle string or array of such within which to search. NULL
   *   stands for no filtering by bundle, i.e. searching among all bundles
   * @param string|array $service_id
   *   Either a single behavior service ID or an array of them within which to
   *   execute the lookup. You may also use the wildcard * to search among all
   *   supported behaviors
   *
   * @return array
   *   Array of looked up synonyms/entities. Each element in this array will be
   *   an object with the following structure:
   *   - synonym: (string) synonym that was looked up
   *   - entity_id: (int) ID of the entity which this synonym belongs to
   */
  public function synonymsFind(Condition $condition, EntityTypeInterface $entity_type, $bundle = NULL, $service_id = 'synonyms.behavior.autocomplete') {
    if (!$entity_type->getKey('bundle')) {
      $bundle = $entity_type->id();
    }

    $lookup = [];

    if ($service_id == '*') {
      $service_id = array_keys($this->behaviorService->getBehaviorServicesWithInterface(SynonymsFindProviderInterface::class));
    }

    if (!is_array($service_id)) {
      $service_id = [$service_id];
    }

    if (is_null($bundle)) {
      $bundle = array_keys($this->entityTypeBundleInfo->getBundleInfo($entity_type->id()));
    }

    foreach ($service_id as $service_id_value) {
      foreach ($this->behaviorService->getSynonymConfigEntities($service_id_value, $entity_type->id(), $bundle) as $synonym_config) {
        foreach ($synonym_config->getProviderPluginInstance()->synonymsFind(clone $condition) as $synonym) {
          $lookup[] = $synonym;
        }
      }
    }

    return $lookup;
  }

}
