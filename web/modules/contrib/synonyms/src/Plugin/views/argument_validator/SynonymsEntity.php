<?php

namespace Drupal\synonyms\Plugin\views\argument_validator;

use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;
use Drupal\synonyms\SynonymsProviderInterface\SynonymsFindProviderInterface;
use Drupal\synonyms\SynonymsService\BehaviorService;
use Drupal\views\Plugin\views\argument_validator\Entity;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Synonyms-friendly entity validator.
 *
 * @ViewsArgumentValidator(
 *   id = "synonyms_entity",
 *   deriver = "Drupal\synonyms\Plugin\Derivative\ViewsSynonymsEntityArgumentValidator"
 * )
 */
class SynonymsEntity extends Entity {

  protected $multipleCapable = FALSE;

  /**
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var QueryFactory
   */
  protected $entityQuery;

  /**
   * @var BehaviorService
   */
  protected $behaviorService;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityManagerInterface $entity_manager, EntityTypeManagerInterface $entity_type_manager, QueryFactory $entity_query, BehaviorService $behavior_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_manager);

    $this->entityTypeManager = $entity_type_manager;
    $this->entityQuery = $entity_query;
    $this->behaviorService = $behavior_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_definition,
      $plugin_definition,
      $container->get('entity.manager'),
      $container->get('entity_type.manager'),
      $container->get('entity.query'),
      $container->get('synonyms.behaviors')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['transform'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Transform dashes in URL to spaces.'),
      '#default_value' => $this->options['transform'],
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validateArgument($argument) {
    if ($this->options['transform']) {
      $argument = str_replace('-', ' ', $argument);
    }

    $entity_type = $this->entityTypeManager->getDefinition($this->definition['entity_type']);

    if ($entity_type->hasKey('label') || $entity_type->id() == 'user') {
      $query = $this->entityQuery->get($entity_type->id());

      // User entity type does not declare its label, while it does have one.
      $label_column = $entity_type->id() == 'user' ? 'name' : $entity_type->getKey('label');
      $query->condition($label_column, $argument, '=');

      if ($entity_type->hasKey('bundle') && !empty($this->options['bundles'])) {
        $query->condition($entity_type->getKey('bundle'), $this->options['bundles'], 'IN');
      }

      $result = $query->execute();
      if (!empty($result)) {
        $entities = $this->entityTypeManager->getStorage($entity_type->id())->loadMultiple($result);
        foreach ($entities as $entity) {
          if ($this->validateEntity($entity)) {
            $this->argument->argument = $entity->id();
            return TRUE;
          }
        }
      }
    }

    // We've fallen through with search by entity name, now it's time to search
    // by synonyms.
    $condition = new Condition('AND');
    $condition->condition(SynonymsFindProviderInterface::COLUMN_SYNONYM_PLACEHOLDER, $argument, '=');

    foreach ($this->behaviorService->getBehaviorServicesWithInterface(SynonymsFindProviderInterface::class) as $service_id => $service) {
      foreach ($this->behaviorService->getSynonymConfigEntities($service_id, $entity_type->id(), empty($this->options['bundles']) ? NULL : $this->options['bundles']) as $synonym_config) {
        foreach ($synonym_config->getProviderPluginInstance()->synonymsFind(clone $condition) as $synonym) {
          $entity = $this->entityTypeManager->getStorage($entity_type->id())->load($synonym->entity_id);
          if ($this->validateEntity($entity)) {
            $this->argument->argument = $entity->id();
            return TRUE;
          }
        }
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['transform'] = array('default' => FALSE);

    return $options;
  }

}
