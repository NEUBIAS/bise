<?php

namespace Drupal\synonyms\Plugin\views\filter;

use Drupal\Core\Entity\Entity;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Drupal\views\ViewExecutable;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Filter entity by its name or one of its synonyms.
 *
 * @ViewsFilter("synonyms_entity")
 */
class SynonymsEntity extends FilterPluginBase {

  /**
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defineOptions() {
    $options = parent::defineOptions();

    $options['widget'] = [
      'default' => 'autocomplete',
    ];

    $options['target_bundles'] = [
      'default' => NULL,
    ];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);

    switch ($this->options['widget']) {
      case 'autocomplete':
        $this->operator = 'IN';
        break;

      case 'select':
        $this->operator = !$this->isExposed() || $this->options['expose']['multiple'] ? 'IN' : '=';
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildExtraOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildExtraOptionsForm($form, $form_state);

    $form['widget'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Widget'),
      '#options' => [
        'autocomplete' => $this->t('Autocomplete'),
        'select' => $this->t('Select'),
      ],
      '#default_value' => $this->options['widget'],
      '#required' => TRUE,
      '#description' => $this->t('Choose what widget to use in order to specify entity.'),
    );

    if ($this->entityTypeManager->getDefinition($this->definition['entity_type'])->hasKey('bundle')) {
      $options = array_map(function($item) {
        return $item['label'];
      }, $this->entityTypeBundleInfo->getBundleInfo($this->definition['entity_type']));

      $form['target_bundles'] = array(
        '#type' => 'checkboxes',
        '#title' => $this->t('Bundles'),
        '#description' => $this->t('Limit the possible values down to specific subset of bundles. Leave empty to have no filter by bundles.'),
        '#default_value' => $this->options['target_bundles'],
        '#options' => $options,
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitExtraOptionsForm($form, FormStateInterface $form_state) {
    parent::submitExtraOptionsForm($form, $form_state);

    if (isset($form['target_bundles'])) {
      $target_bundles = array_values(array_filter($form_state->getValue($form['target_bundles']['#parents'])));
      if (empty($target_bundles)) {
        $target_bundles = NULL;
      }
      $form_state->setValueForElement($form['target_bundles'], $target_bundles);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function hasExtraOptions() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function validateExposed(&$form, FormStateInterface $form_state) {
    parent::validateExposed($form, $form_state);

    $identifier = $this->options['expose']['identifier'];

    $target_ids = $form_state->getValue($identifier);
    switch ($this->operator) {
      case 'IN':
        $target_ids = array_map(function($item) {
          return $item['target_id'];
        }, $target_ids);
        break;

      case '=':
        $target_ids = $target_ids['target_id'];
        break;
    }

    $form_state->setValue($identifier, $target_ids);
  }

  /**
   * {@inheritdoc}
   */
  public function adminSummary() {
    if ($this->isExposed()) {
      return $this->t('Exposed');
    }

    $labels = [];

    if (!empty($this->value)) {
      foreach ($this->getEntityStorage()->loadMultiple($this->value) as $entity) {
        $labels[] = $entity->label();
      }
    }

    return $this->operator . ' ' . implode(', ', $labels);
  }

  /**
   * {@inheritdoc}
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    if (!empty($this->value)) {
      $default_value = $this->getEntityStorage()->loadMultiple($this->value);
    }
    else {
      $default_value = [];
    }

    switch ($this->options['widget']) {
      case 'autocomplete':
        $form['value'] = array(
          '#type' => 'synonyms_entity_autocomplete',
          '#title' => $this->t('Entity'),
          '#target_type' => $this->definition['entity_type'],
          '#target_bundles' => $this->options['target_bundles'],
          '#default_value' => $default_value,
        );
        break;

      case 'select':
        $form['value'] = array(
          '#type' => 'synonyms_entity_select',
          '#title' => $this->t('Entity'),
          '#target_type' => $this->definition['entity_type'],
          '#handler_settings' => [
            'target_bundles' => $this->options['target_bundles'],
          ],
          '#default_value' => array_keys($default_value),
          '#multiple' => TRUE,
          '#empty_option' => '',
        );
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function valueSubmit($form, FormStateInterface $form_state) {
    parent::valueSubmit($form, $form_state);

    $target_ids = array_map(function($item) {
      return $item['target_id'];
    }, $form_state->getValue($form['value']['#parents']));

    $form_state->setValueForElement($form['value'], $target_ids);
  }

  /**
   * Get entity storage of the entity type this filter is set up to use.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface
   */
  protected function getEntityStorage() {
    return $this->entityTypeManager->getStorage($this->definition['entity_type']);
  }

}
