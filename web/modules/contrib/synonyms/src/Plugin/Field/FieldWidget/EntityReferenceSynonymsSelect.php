<?php

namespace Drupal\synonyms\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'synonyms friendly select' widget.
 *
 * @FieldWidget(
 *   id = "synonyms_select",
 *   label = @Translation("Synonyms-friendly select"),
 *   description = @Translation("A dropdown with entities and their synonyms."),
 *   field_types = {
 *     "entity_reference"
 *   },
 *   multiple_values = TRUE
 * )
 */
class EntityReferenceSynonymsSelect extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $default_value = [];
    foreach ($items as $item) {
      if ($item->{$this->getKeyColumn()}) {
        $default_value[] = $item->{$this->getKeyColumn()};
      }
    }

    $element += array(
      '#type' => 'synonyms_entity_select',
      '#key_column' => $this->getKeyColumn(),
      '#target_type' => $this->getFieldSetting('target_type'),
      '#handler' => $this->fieldDefinition->getSetting('handler'),
      '#handler_settings' => $this->fieldDefinition->getSetting('handler_settings') ?: array(),
      '#multiple' => $this->fieldDefinition->getFieldStorageDefinition()->isMultiple(),
      '#default_value' => $this->fieldDefinition->getFieldStorageDefinition()->isMultiple() ? $default_value : reset($default_value),
    );
    return $element;
  }

  /**
   * Get name of the column that is managed by this widget in the field
   * .
   * @return string
   */
  protected function getKeyColumn() {
    return $this->fieldDefinition->getFieldStorageDefinition()->getPropertyNames()[0];
  }

}
