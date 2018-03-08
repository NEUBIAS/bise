<?php

/**
 * @file
 * Documentation for Synonyms module.
 */

/**
 * Hook to alter known simple field types that contain potential synonyms data.
 *
 * The simple field types (those defined by $map) are used for synonyms
 * providing through Drupal\synonyms\Plugin\Synonyms\Provider\Field or
 * Drupal\synonyms\Plugin\Synonyms\Provider\BaseField plugins depending whether
 * it is an attached field or base one correspondingly. Both synonyms providers
 * plugin simply take a specific column/value from the field and return it as a
 * synonym.
 *
 * @param array $map
 *   Array of known simple field types eligible for synonyms providing through
 *   the 2 plugins. Keys are field types whereas corresponding values are field
 *   columns that contain synonyms. You are encouraged to alter $map in order to
 *   add/remove known field types per your business needs
 */
function hook_synonyms_field_type_to_synonym_alter(&$map) {
  // Let's assume our module provides some additional field type and we want
  // that field type to be eligible for synonyms providing through the 2 simple
  // plugins. And let's suppose the synonyms are actually stored in 'value'
  // column within our custom field type.
  $map['the_field_type_my_module_provides'] = 'value';
}
