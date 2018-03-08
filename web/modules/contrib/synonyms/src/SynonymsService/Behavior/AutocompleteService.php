<?php

namespace Drupal\synonyms\SynonymsService\Behavior;

use Drupal\Component\Utility\Html;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\KeyValueStore\KeyValueStoreInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\synonyms\SynonymInterface;
use Drupal\synonyms\SynonymsProviderInterface\SynonymsFindProviderInterface;
use Drupal\synonyms\SynonymsProviderInterface\SynonymsFormatWordingProviderInterface;
use Drupal\synonyms\SynonymsProviderInterface\SynonymsGetProviderInterface;
use Drupal\synonyms\SynonymsService\BehaviorService;

/**
 * Synonyms behavior service for autocomplete.
 */
class AutocompleteService implements SynonymsBehaviorConfigurableInterface {

  use StringTranslationTrait;

  /**
   * @var KeyValueStoreInterface
   */
  protected $keyValue;

  /**
   * The entity reference selection handler plugin manager.
   *
   * @var SelectionPluginManagerInterface
   */
  protected $selectionManager;

  /**
   * @var BehaviorService
   */
  protected $behaviorService;

  /**
   * @var Connection
   */
  protected $database;

  /**
   * @var RendererInterface
   */
  protected $renderer;

  /**
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  public function __construct(KeyValueFactoryInterface $key_value, SelectionPluginManagerInterface $selection_plugin_manager, BehaviorService $behavior_service, Connection $database, EntityTypeManagerInterface $entity_type_manager, RendererInterface $renderer) {
    $this->keyValue = $key_value->get('synonyms_entity_autocomplete');
    $this->selectionManager = $selection_plugin_manager;
    $this->behaviorService = $behavior_service;
    $this->database = $database;
    $this->entityTypeManager = $entity_type_manager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, array $configuration, SynonymInterface $synonym_config) {
    $replacements = [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#items' => [],
    ];
    foreach ($synonym_config->getProviderPluginInstance()->formatWordingAvailableTokens() as $token => $token_info) {
      $replacements['#items'][] = Html::escape($token) . ': ' . $token_info;
    }

    $replacements = $this->renderer->renderRoot($replacements);

    $form['wording'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Wording for autocomplete suggestion'),
      '#default_value' => $configuration['wording'],
      '#description' => $this->t('Specify the wording with which the autocomplete suggestion should be presented. Available replacement tokens are: @replacements', [
        '@replacements' => $replacements,
      ]),
      '#required' => TRUE,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state, SynonymInterface $synonym_config) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state, SynonymInterface $synonym_config) {
    return [
      'wording' => $form_state->getValue('wording'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Autocomplete');
  }

  /**
   * {@inheritdoc}
   */
  public function getRequiredInterfaces() {
    return [
      SynonymsGetProviderInterface::class,
      SynonymsFindProviderInterface::class,
      SynonymsFormatWordingProviderInterface::class,
    ];
  }

  /**
   * Execute synonym-friendly lookup of entities by a given keyword.
   *
   * @param string $keyword
   *   Keyword to search for
   * @param string $key_value_key
   *   Key under which additional settings about the lookup are stored in
   *   key-value storage
   *
   * @return array
   *   Array of looked up suggestions. Each array will have the following
   *   structure:
   *   - entity_id: (int) ID of the entity which this entry represents
   *   - entity_label: (string) Label of the entity which this entry represents
   *   - wording: (string) Wording with which this entry should be shown to the
   *     end user on the UI
   */
  public function autocompleteLookup($keyword, $key_value_key) {
    $suggestions = [];

    if ($this->keyValue->has($key_value_key)) {
      $settings = $this->keyValue->get($key_value_key);

      $suggested_entity_ids = [];

      $target_bundles = $settings['target_bundles'];
      $handler_settings = [];
      if (!empty($target_bundles)) {
        $handler_settings['target_bundles'] = $target_bundles;
      }
      elseif (!$this->entityTypeManager->getDefinition($settings['target_type'])->hasKey('bundle')) {
        $target_bundles = [$settings['target_type']];
      }

      $options = array(
        'target_type' => $settings['target_type'],
        'handler' => 'default',
        'handler_settings' => $handler_settings,
      );
      $handler = $this->selectionManager->getInstance($options);

      foreach ($handler->getReferenceableEntities($keyword, $settings['match'], $settings['suggestion_size']) as $suggested_entities) {
        foreach ($suggested_entities as $entity_id => $entity_label) {
          $suggestions[] = [
            'entity_id' => $entity_id,
            'entity_label' => $entity_label,
            'wording' => $entity_label,
          ];
          if ($settings['suggest_only_unique']) {
            $suggested_entity_ids[] = $entity_id;
          }
        }
      }

      if (count($suggestions) < $settings['suggestion_size']) {
        foreach ($this->behaviorService->getSynonymConfigEntities('synonyms.behavior.autocomplete', $settings['target_type'], $target_bundles) as $behavior_service) {
          $plugin_instance = $behavior_service->getProviderPluginInstance();

          $condition = new Condition('AND');
          switch ($settings['match']) {
            case 'CONTAINS':
              $condition->condition(SynonymsFindProviderInterface::COLUMN_SYNONYM_PLACEHOLDER, '%' . $this->database->escapeLike($keyword) . '%', 'LIKE');
              break;

            case 'STARTS_WITH':
              $condition->condition(SynonymsFindProviderInterface::COLUMN_SYNONYM_PLACEHOLDER, $this->database->escapeLike($keyword) . '%', 'LIKE');
              break;
          }

          if (!empty($suggested_entity_ids)) {
            $condition->condition(SynonymsFindProviderInterface::COLUMN_ENTITY_ID_PLACEHOLDER, $suggested_entity_ids, 'NOT IN');
          }


          foreach ($plugin_instance->synonymsFind($condition) as $row) {
            if (!in_array($row->entity_id, $suggested_entity_ids)) {
              $suggestions[] = [
                'entity_id' => $row->entity_id,
                'entity_label' => NULL,
                'synonym' => $row->synonym,
                'synonym_config_entity' => $behavior_service,
                'wording' => NULL,
              ];
            }

            if ($settings['suggest_only_unique']) {
              $suggested_entity_ids[] = $row->entity_id;
            }

            if (count($suggestions) == $settings['suggestion_size']) {
              break(2);
            }
          }
        }
      }

      $ids = [];
      foreach ($suggestions as $suggestion) {
        if (!$suggestion['entity_label']) {
          $ids[] = $suggestion['entity_id'];
        }
      }
      $ids = array_unique($ids);

      if (!empty($ids)) {
        $entities = $this->entityTypeManager->getStorage($settings['target_type'])
          ->loadMultiple($ids);

        foreach ($suggestions as $k => $suggestion) {
          if (!$suggestion['entity_label']) {
            $suggestions[$k]['entity_label'] = $entities[$suggestion['entity_id']]->label();
            $suggestions[$k]['wording'] = $suggestion['synonym_config_entity']->getProviderPluginInstance()->synonymFormatWording($suggestion['synonym'], $entities[$suggestion['entity_id']], $suggestion['synonym_config_entity']);
          }
        }
      }
    }

    return $suggestions;
  }

}
