<?php

namespace Drupal\views_bulk_edit\Plugin\Action;

use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\views_bulk_operations\Action\ViewsBulkOperationsPreconfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\views\Views;
use Drupal\views_bulk_operations\Service\ViewsbulkOperationsViewData;
use Drupal\views_bulk_operations\Service\ViewsBulkOperationsActionProcessor;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Database\Connection;

/**
 * Modify entity field values.
 *
 * @Action(
 *   id = "views_bulk_edit",
 *   label = @Translation("Modify field values"),
 *   type = ""
 * )
 */
class ModifyEntityValues extends ViewsBulkOperationsActionBase implements ContainerFactoryPluginInterface, ViewsBulkOperationsPreconfigurationInterface {

  /**
   * Database conection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Object constructor.
   *
   * @param array $configuration
   *   Plugin configuration.
   * @param string $plugin_id
   *   The plugin Id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\views_bulk_operations\Service\ViewsbulkOperationsViewData $viewDataService
   *   The VBO view data service.
   * @param \Drupal\views_bulk_operations\Service\ViewsBulkOperationsActionProcessor $actionProcessor
   *   The VBO action processor.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $bundleInfo
   *   Bundle info object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ViewsbulkOperationsViewData $viewDataService, ViewsBulkOperationsActionProcessor $actionProcessor, EntityTypeManagerInterface $entityTypeManager, EntityTypeBundleInfoInterface $bundleInfo, Connection $database) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->viewDataService = $viewDataService;
    $this->actionProcessor = $actionProcessor;
    $this->entityTypeManager = $entityTypeManager;
    $this->bundleInfo = $bundleInfo;
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('views_bulk_operations.data'),
      $container->get('views_bulk_operations.processor'),
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildPreConfigurationForm(array $form, array $values, FormStateInterface $form_state) {
    $form['get_bundles_from_results'] = [
      '#title' => $this->t('Get entity bundles from results'),
      '#type' => 'checkbox',
      '#default_value' => isset($values['get_bundles_from_results']) ? $values['get_bundles_from_results'] : TRUE,
      '#description' => $this->t('NOTE: If performance issues are observed when using "All results in this view" selector in case of large result sets, uncheck this and use a bundle filter (node type, taxonomy vocabulary etc.) on the view.'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    // Get view bundles.
    $bundle_data = $this->getViewBundles();

    // Store entity data.
    $storage = $form_state->getStorage();
    $storage['vbe_entity_bundles_data'] = $bundle_data;
    $form_state->setStorage($storage);

    $form['#attributes']['class'] = ['views-bulk-edit-form'];
    $form['#attached']['library'][] = 'views_bulk_edit/views_bulk_edit.edit_form';

    foreach ($bundle_data as $entity_type_id => $bundles) {
      foreach ($bundles as $bundle => $label) {
        $form = $this->getBundleForm($entity_type_id, $bundle, $label, $form, $form_state);
      }
    }

    return $form;
  }

  /**
   * Helper method to get bundles displayed by the view.
   *
   * @return array
   *   Array of entity bundles returned by the current view
   *   keyed by entity type IDs.
   */
  protected function getViewBundles() {

    // Get a list of all entity types and bundles of the view.
    $bundle_data = [];
    $bundle_info = $this->bundleInfo->getAllBundleInfo();

    // If the list of selected results is available,
    // query db for selected bundles.
    if (!empty($this->context['list'])) {
      $query_data = [];
      foreach ($this->context['list'] as $item) {
        list(,, $entity_type_id, $id,) = $item;
        $query_data[$entity_type_id][$id] = $id;
      }
      foreach ($query_data as $entity_type_id => $entity_ids) {
        $entityTypeDefinition = $this->entityTypeManager->getDefinition($entity_type_id);
        if ($bundle_key = $entityTypeDefinition->getKey('bundle')) {
          $id_key = $entityTypeDefinition->getKey('id');

          $results = $this->database->select($entityTypeDefinition->getBaseTable(), 'base')
            ->fields('base', [$bundle_key])
            ->condition($id_key, $entity_ids, 'IN')
            ->execute()
            ->fetchCol();

          foreach ($results as $bundle_id) {
            if (!isset($bundle_data[$entity_type_id][$bundle_id])) {
              $bundle_data[$entity_type_id][$bundle_id] = $bundle_info[$entity_type_id][$bundle_id]['label'];
            }
          }
        }
        else {
          $bundle_data[$entity_type_id][$entity_type_id] = '';
        }
      }
    }

    // If not, fallback to other methods.
    else {
      // Initialize view and VBO view data service.
      $view = Views::getView($this->context['view_id']);
      $view->setDisplay($this->context['display_id']);
      if (!empty($this->context['arguments'])) {
        $view->setArguments($this->context['arguments']);
      }
      if (!empty($this->context['exposed_input'])) {
        $view->setExposedInput($this->context['exposed_input']);
      }
      $view->build();

      $this->viewDataService->init($view, $view->getDisplay(), $this->context['relationship_id']);

      // If administrator chose this method, get bundles from actual
      // view results.
      // NOTE: This can cause performance problems in case of large result sets!
      if (!empty($this->context['preconfiguration']['get_bundles_from_results'])) {
        $entities = [];
        if (empty($this->context['list'])) {
          $view->query->setLimit(0);
          $view->query->setOffset(0);
          $view->query->execute($view);

          foreach ($view->result as $row) {
            $entities[] = $this->viewDataService->getEntity($row);
          }

        }
        else {
          foreach ($this->context['list'] as $item) {
            $entities[] = $this->actionProcessor->getEntity($item);
          }
        }

        if (!empty($entities)) {
          foreach ($entities as $entity) {
            $entity_type_id = $entity->getEntityTypeId();
            $bundle = $entity->bundle();
            if (!isset($bundle_data[$entity_type_id][$bundle])) {
              $bundle_data[$entity_type_id][$bundle] = $bundle_info[$entity_type_id][$bundle]['label'];
            }
          }
        }
      }

      // The painless way, may display form for more bundles
      // than the actual result set has.
      else {
        // Try to get possible bundles from a bundle filter, fixed or exposed,
        // if exists (hopefully).
        foreach ($this->viewDataService->getEntityTypeIds() as $entity_type_id) {
          $entityTypeDefinition = $this->entityTypeManager->getDefinition($entity_type_id);
          $bundle_key = $entityTypeDefinition->getKey('bundle');
          if (isset($view->filter[$bundle_key]) && !empty($view->filter[$bundle_key]->value)) {
            foreach ($view->filter[$bundle_key]->value as $bundle) {
              $bundle_data[$entity_type_id][$bundle] = $bundle_info[$entity_type_id][$bundle]['label'];
            }
          }

          // If previous failed and admin did not set to get bundles
          // from view results, get all bundles of displayed entity types.
          elseif (empty($this->context['preconfiguration']['get_bundles_from_results'])) {
            if (isset($bundle_info[$entity_type_id])) {
              foreach ($bundle_info[$entity_type_id] as $bundle => $label) {
                $bundle_data[$entity_type_id][$bundle] = $bundle_info[$entity_type_id][$bundle]['label'];
              }
            }
          }
        }
      }
    }
    return $bundle_data;
  }

  /**
   * Gets the form for this entity display.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param string $bundle
   *   The bundle ID.
   * @param mixed $bundle_label
   *   Bundle label.
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form_state object.
   *
   * @return array
   *   Edit form for the current entity bundle.
   */
  protected function getBundleForm($entity_type_id, $bundle, $bundle_label, array $form, FormStateInterface $form_state) {
    $entityType = $this->entityTypeManager->getDefinition($entity_type_id);
    $entity = $this->entityTypeManager->getStorage($entity_type_id)->create([
      $entityType->getKey('bundle') => $bundle,
    ]);

    if (!isset($form[$entity_type_id])) {
      $form[$entity_type_id] = [
        '#type' => 'container',
        '#tree' => TRUE,
      ];
    }

    // If there is no bundle label, the entity has no bundles.
    if (empty($bundle_label)) {
      $bundle_label = $entityType->getLabel();
    }
    $form[$entity_type_id][$bundle] = [
      '#type' => 'fieldset',
      '#title' => $entityType->getLabel() . ' - ' . $bundle_label,
      '#parents' => [$entity_type_id, $bundle],
    ];

    $form_display = EntityFormDisplay::collectRenderDisplay($entity, 'bulk_edit');
    $form_display->buildForm($entity, $form[$entity_type_id][$bundle], $form_state);

    $form[$entity_type_id][$bundle] += $this->getSelectorForm($entity_type_id, $bundle, $form[$entity_type_id][$bundle]);

    return $form;
  }

  /**
   * Builds the selector form.
   *
   * Given an entity form, create a selector form to provide options to update
   * values.
   *
   * @param string $bundle
   *   The bundle machine name.
   * @param array $form
   *   The form we're building the selection options for.
   *
   * @return array
   *   The new selector form.
   */
  protected function getSelectorForm($entity_type_id, $bundle, array &$form) {
    $selector['_field_selector'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Select fields to change'),
      '#weight' => -50,
      '#tree' => TRUE,
    ];

    foreach (Element::children($form) as $key) {
      if (isset($form[$key]['#access']) && !$form[$key]['#access']) {
        continue;
      }
      if ($key == '_field_selector' || !$element = &$this->findFormElement($form[$key])) {
        continue;
      }

      $element['#required'] = FALSE;
      $element['#tree'] = TRUE;

      // Add the toggle field to the form.
      $selector['_field_selector'][$key] = [
        '#type' => 'checkbox',
        '#title' => $element['#title'],
        '#tree' => TRUE,
      ];

      // Force the original value to be hidden unless the checkbox is enabled.
      $form[$key]['#states'] = [
        'visible' => [
          sprintf('[name="%s[%s][_field_selector][%s]"]', $entity_type_id, $bundle, $key) => ['checked' => TRUE],
        ],
      ];
    }

    if (empty(Element::children($selector['_field_selector']))) {
      $selector['_field_selector']['#title'] = $this->t('There are no fields available to modify');
    }

    return $selector;
  }

  /**
   * Finds the deepest most form element and returns it.
   *
   * @param array $form
   *   The form element we're searching.
   *
   * @return array|null
   *   The deepest most element if we can find it.
   */
  protected function &findFormElement(array &$form) {
    foreach (Element::children($form) as $key) {
      if (isset($form[$key]['#title']) && isset($form[$key]['#type'])) {
        return $form[$key];
      }
      elseif (is_array($form[$key])) {
        $element = &$this->findFormElement($form[$key]);
        return $element;
      }
    }
    return NULL;
  }

  /**
   * Provides same functionality as ARRAY_FILTER_USE_KEY for PHP 5.5.
   *
   * @param array $array
   *   The array of data to filter.
   * @param callable $callback
   *   The function we're going to use to determine the filtering.
   *
   * @return array
   *   The filtered data.
   */
  protected function filterOnKey(array $array, callable $callback) {
    $filtered_values = [];
    foreach ($array as $key => $value) {
      if ($callback($key)) {
        $filtered_values[$key] = $value;
      }
    }
    return $filtered_values;
  }

  /**
   * Save modified entity field values to action configuration.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form_state object.
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $storage = $form_state->getStorage();
    $bundle_data = $storage['vbe_entity_bundles_data'];

    foreach ($bundle_data as $entity_type_id => $bundles) {
      $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
      foreach ($bundles as $bundle => $label) {
        $field_data = $form_state->getValue([$entity_type_id, $bundle]);
        $modify = array_filter($field_data['_field_selector']);
        if (!empty($modify)) {
          $form_clone = $form;
          $form_clone['#parents'] = [$entity_type_id, $bundle];
          $entity = $this->entityTypeManager->getStorage($entity_type_id)->create([
            $entity_type->getKey('bundle') => $bundle,
          ]);
          $form_display = EntityFormDisplay::collectRenderDisplay($entity, 'bulk_edit');
          $form_display->extractFormValues($entity, $form_clone, $form_state);

          foreach (array_keys($modify) as $field) {
            $this->configuration[$entity_type_id][$bundle][$field] = $entity->{$field}->getValue();
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    $type_id = $entity->getEntityTypeId();
    $bundle = $entity->bundle();

    $result = $this->t('Skip (field is not present on this bundle)');
    if (isset($this->configuration[$type_id][$bundle])) {
      foreach ($this->configuration[$type_id][$bundle] as $field => $value) {
        $entity->{$field}->setValue($value);
      }
      $entity->save();
      $result = $this->t('Modify filed values');
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    $access = $object->access('update', $account, TRUE);
    return $return_as_object ? $access : $access->isAllowed();
  }

}
