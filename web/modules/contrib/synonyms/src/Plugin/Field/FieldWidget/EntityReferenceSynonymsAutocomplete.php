<?php

namespace Drupal\synonyms\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\synonyms\SynonymsService\BehaviorService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'synonyms friendly autocomplete' widget.
 *
 * @FieldWidget(
 *   id = "synonyms_autocomplete",
 *   label = @Translation("Synonyms-friendly autocomplete"),
 *   description = @Translation("An autocomplete with entities and their synonyms."),
 *   field_types = {
 *     "entity_reference"
 *   },
 *   multiple_values = TRUE
 * )
 */
class EntityReferenceSynonymsAutocomplete extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * @var BehaviorService
   */
  protected $behaviorService;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, BehaviorService $behavior_service) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);

    $this->behaviorService = $behavior_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('synonyms.behaviors')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'suggestion_size' => 10,
      'suggest_only_unique' => FALSE,
      'match' => 'CONTAINS',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['suggestion_size'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Suggestions Size'),
      '#description' => $this->t('Please, enter how many suggested entities to show in the autocomplete textfield.'),
      '#required' => TRUE,
      '#default_value' => $this->getSetting('suggestion_size'),
    );

    $elements['suggest_only_unique'] = array(
      '#type' => 'checkbox',
      '#title' => t('Suggest only one entry per entity'),
      '#description' => t('If you want to include only name or a single synonym, suggesting a particular entity, while disregarding all ongoing ones, please, tick this checkbox on.'),
      '#default_value' => $this->getSetting('suggest_only_unique'),
    );

    $elements['match'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Match operator'),
      '#description' => $this->t('Choose how to match the keyword against existing data.'),
      '#options' => $this->getMatchOperatorOptions(),
      '#default_value' => $this->getSetting('match'),
      '#required' => TRUE,
    );

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $summary[] = $this->t('Suggestion size: @size', [
      '@size' => $this->getSetting('suggestion_size'),
    ]);

    $summary[] = $this->t('Only unique: @unique', [
      '@unique' => $this->getSetting('suggest_only_unique') ? $this->t('Yes') : $this->t('No'),
    ]);

    $summary[] = $this->t('Match: @match', [
      '@match' => $this->getMatchOperatorOptions()[$this->getSetting('match')],
    ]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $default_value = [];
    foreach ($items as $item) {
      if ($item->entity) {
        $default_value[] = $item->entity;
      }
    }
    $element += array(
      '#type' => 'synonyms_entity_autocomplete',
      '#target_type' => $this->getFieldSetting('target_type'),
      '#target_bundles' => $this->getFieldSetting('handler_settings')['target_bundles'],
      '#suggestion_size' => $this->getSetting('suggestion_size'),
      '#suggest_only_unique' => $this->getSetting('suggest_only_unique'),
      '#match' => $this->getSetting('match'),
      '#default_value' => $default_value,
    );

    return $element;
  }

  /**
   * Returns the options for the match operator.
   *
   * @return array
   *   List of options.
   */
  protected function getMatchOperatorOptions() {
    return [
      'STARTS_WITH' => t('Starts with'),
      'CONTAINS' => t('Contains'),
    ];
  }

}
