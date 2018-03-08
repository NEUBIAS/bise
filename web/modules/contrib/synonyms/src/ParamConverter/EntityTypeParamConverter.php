<?php

namespace Drupal\synonyms\ParamConverter;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\ParamConverter\ParamConverterInterface;
use Symfony\Component\Routing\Route;

/**
 * Param converter service for entity types.
 */
class EntityTypeParamConverter implements ParamConverterInterface {

  /**
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * EntityTypeParamConverter constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults) {
    return $this->entityTypeManager->getDefinition($value);
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route) {
    return (!empty($definition['type']) && $definition['type'] == 'synonyms_entity_type');
  }

}
