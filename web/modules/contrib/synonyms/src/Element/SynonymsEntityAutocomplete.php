<?php

namespace Drupal\synonyms\Element;

use Drupal\Component\Utility\Crypt;
use Drupal\Component\Utility\Tags;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Textfield;
use Drupal\Core\Site\Settings;

/**
 * Form element for synonyms-friendly entity autocomplete.
 *
 * @FormElement("synonyms_entity_autocomplete")
 */
class SynonymsEntityAutocomplete extends Textfield {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $info = parent::getInfo();

    // Target entity type for suggestions.
    $info['#target_type'] = NULL;

    // String or array of allowed target bundles. If omitted, all bundles will
    // be included in autocomplete suggestions.
    $info['#target_bundles'] = NULL;

    // Default maximum amount of provided suggestions.
    $info['#suggestion_size'] = 10;

    // Whether to suggest same entity at most once (in the case, when more than
    // 1 synonym triggers inclusion of that entity).
    $info['#suggest_only_unique'] = FALSE;

    // Operator to match keyword. Allowed values are:
    // - CONTAINS
    // - STARTS_WITH
    $info['#match'] = 'CONTAINS';

    array_unshift($info['#process'], [get_class($this), 'elementSynonymsEntityAutocomplete']);
    $info['#element_validate'][] = [get_class($this), 'validateEntityAutocomplete'];
    return $info;
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    $entities = NULL;
    if ($input === FALSE) {
      if (isset($element['#default_value'])) {
        $entities = [];
        foreach ($element['#default_value'] as $entity) {
          $entities[] = $entity;
        }
      }
    }
    // Potentially the #value is set directly, so it contains the 'target_id'
    // array structure instead of a string.
    elseif ($input !== FALSE && is_array($input)) {
      $entity_ids = array_map(function(array $item) {
        return $item['target_id'];
      }, $input);
      $entities = \Drupal::entityTypeManager()->getStorage($element['#target_type'])->loadMultiple($entity_ids);
    }

    if (is_array($entities)) {
      $value = [];
      foreach ($entities as $entity) {
        $value[] = $entity->label() . ' (' . $entity->id() . ')';
      }
      return Tags::implode($value);
    }
  }

  /**
   * Form element process callback for 'synonyms_entity_autocomplete' type.
   */
  public static function elementSynonymsEntityAutocomplete(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $data = [
      'target_type' => $element['#target_type'],
      'target_bundles' => $element['#target_bundles'],
      'suggestion_size' => $element['#suggestion_size'],
      'suggest_only_unique' => $element['#suggest_only_unique'],
      'match' => $element['#match'],
    ];
    $token = Crypt::hmacBase64(serialize($data), Settings::getHashSalt());
    $key_value_storage = \Drupal::keyValue('synonyms_entity_autocomplete');

    $key_value_storage->setIfNotExists($token, $data);

    $element['#autocomplete_route_name'] = 'synonyms.entity_autocomplete';
    $element['#autocomplete_route_parameters'] = [
      'target_type' => $element['#target_type'],
      'token' => $token,
    ];

    return $element;
  }

  /**
   * Form element validation handler for synonyms_entity_autocomplete elements.
   */
  public static function validateEntityAutocomplete(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $tokens = Tags::explode($form_state->getValue($element['#parents']));
    $value = [];

    $autocomplete_service = \Drupal::getContainer()->get('synonyms.behavior.autocomplete');
    foreach ($tokens as $token) {
      $entity_id = self::extractEntityIdFromAutocompleteInput($token);
      if (!$entity_id) {
        $lookup = $autocomplete_service->autocompleteLookup($token, $element['#autocomplete_route_parameters']['token']);
        $lookup = array_shift($lookup);
        if ($lookup) {
          $entity_id = $lookup['entity_id'];
        }
      }

      if ($entity_id) {
        $value[] = ['target_id' => $entity_id];
      }
    }
    $form_state->setValueForElement($element, $value);
  }

  /**
   * Extracts the entity ID from the autocompletion result.
   *
   * @param string $input
   *   The input coming from the autocompletion result.
   *
   * @return mixed|null
   */
  public static function extractEntityIdFromAutocompleteInput($input) {
    $match = NULL;

    // Take "label (entity id)', match the ID from parenthesis when it's a
    // number.
    if (preg_match("/.+\s\((\d+)\)/", $input, $matches)) {
      $match = $matches[1];
    }
    return $match;
  }

}
