<?php

namespace Drupal\synonyms\SynonymsService;

use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\synonyms\SynonymsProviderInterface\SynonymsFindProviderInterface;

/**
 * Service to look up an entity by its name or synonym.
 */
class EntityGet {

  /**
   * @var SynonymsFind
   */
  protected $synonymsFindService;

  /**
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var QueryFactory
   */
  protected $queryFactory;

  public function __construct(SynonymsFind $synonyms_find_service, EntityTypeManagerInterface $entity_type_manager, QueryFactory $query_factory) {
    $this->synonymsFindService = $synonyms_find_service;
    $this->entityTypeManager = $entity_type_manager;
    $this->queryFactory = $query_factory;
  }

  /**
   * Try finding an entity by its name or synonym.
   *
   * @param EntityTypeInterface $entity_type
   *   What entity type is being searched
   * @param string $name
   *   The look up keyword (the supposed name or synonym)
   * @param string $bundle
   *   Optionally limit the search within a specific bundle name of the provided
   *   entity type
   *
   * @return int
   *   ID of the looked up entity. If such entity was not found, then 0 is
   *   returned
   */
  public function entityGetBySynonym(EntityTypeInterface $entity_type, $name, $bundle = NULL) {
    if ($entity_type->id() == 'user' || $entity_type->hasKey('label')) {
      $label_column = $entity_type->id() == 'user' ? 'name' : $entity_type->getKey('label');
      $query = $this->queryFactory->get($entity_type->id());
      $query->condition($label_column, $name);
      if ($entity_type->hasKey('bundle') && $bundle) {
        $query->condition($entity_type->getKey('bundle'), $bundle);
      }

      $result = $query->execute();
      $result = reset($result);
      if ($result) {
        return $result;
      }
    }

    $condition = new Condition('AND');
    $condition->condition(SynonymsFindProviderInterface::COLUMN_SYNONYM_PLACEHOLDER, $name);

    $synonyms_found = $this->synonymsFindService->synonymsFind($condition, $entity_type, $bundle, '*');
    if (isset($synonyms_found[0]->entity_id)) {
      return $synonyms_found[0]->entity_id;
    }

    return 0;
  }

}
