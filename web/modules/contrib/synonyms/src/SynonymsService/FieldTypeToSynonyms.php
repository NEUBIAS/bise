<?php

namespace Drupal\synonyms\SynonymsService;

use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Service to map known field types to how synonyms are encoded in them.
 */
class FieldTypeToSynonyms {

  /**
   * @var ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * FieldTypeToSynonyms constructor.
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }

  /**
   * Map field types to the properties within them where synonyms are stored.
   *
   * @return array
   *   Map where keys are simple field types and values are the properties where
   *   the corresponding field type keeps the synonyms
   */
  public function getSimpleFieldTypeToPropertyMap() {
    $map = [
      'integer' => 'value',
      'float' => 'value',
      'decimal' => 'value',
      'string' => 'value',
      'email' => 'value',
      'telephone' => 'value',
    ];
    $this->moduleHandler->alter('synonyms_field_type_to_synonym', $map);
    return $map;
  }

}
