<?php

namespace Drupal\synonyms\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\synonyms\SynonymInterface;
use Drupal\synonyms\SynonymsProviderPluginManager;
use Drupal\synonyms\SynonymsService\Behavior\SynonymsBehaviorConfigurableInterface;
use Drupal\synonyms\SynonymsService\BehaviorService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Entity form for 'synonym' config entity type.
 */
class SynonymForm extends EntityForm {

  /**
   * @var SynonymInterface
   */
  protected $entity;

  /**
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;

  /**
   * Entity type manager.
   *
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Entity type bundle info.
   *
   * @var EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * @var SynonymsProviderPluginManager
   */
  protected $synonymsProviderPluginManager;

  /**
   * @var BehaviorService
   */
  protected $behaviorServices;

  /**
   * Entity type that is being edited/added.
   *
   * @var EntityTypeInterface
   */
  protected $controlledEntityType;

  /**
   * Bundle that is being edited/added.
   *
   * @var string
   */
  protected $controlledBundle;

  /**
   * Service ID that is being edited/added.
   *
   * @var string
   */
  protected $behaviorServiceId;

  /**
   * @var ContainerInterface
   */
  protected $container;

  public function __construct(QueryFactory $entity_query, EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info, SynonymsProviderPluginManager $synonyms_provider_plugin_manager, BehaviorService $behavior_services, ContainerInterface $container) {
    $this->entityQuery = $entity_query;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
    $this->synonymsProviderPluginManager = $synonyms_provider_plugin_manager;
    $this->behaviorServices = $behavior_services;
    $this->container = $container;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query'),
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('plugin.manager.synonyms_provider'),
      $container->get('synonyms.behaviors'),
      $container
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function init(FormStateInterface $form_state) {
    parent::init($form_state);

    if ($this->entity->isNew()) {
      $this->controlledEntityType = $this->getRequest()->get('synonyms_entity_type')->id();
      $this->controlledBundle = $this->getRequest()->get('bundle');
      $this->behaviorServiceId = $this->getRouteMatch()->getRawParameter('synonyms_behavior_service');
    }
    else {
      $plugin_definition = $this->entity->getProviderPluginInstance()->getPluginDefinition();
      $this->controlledEntityType = $plugin_definition['controlled_entity_type'];
      $this->controlledBundle = $plugin_definition['controlled_bundle'];
      $this->behaviorServiceId = $plugin_definition['synonyms_behavior_service'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $class = get_class($this);

    $provider_plugin = $this->entity->getProviderPlugin();
    if ($form_state->getValue('provider_plugin')) {
      $provider_plugin = $form_state->getValue('provider_plugin');
    }

    $form['id'] = array(
      '#type' => 'value',
      '#value' => str_replace(':', '.', $provider_plugin),
    );

    $options = [];
    foreach ($this->synonymsProviderPluginManager->getDefinitions() as $plugin_id => $plugin) {
      if ($plugin['controlled_entity_type'] ==  $this->controlledEntityType && $plugin['controlled_bundle'] == $this->controlledBundle && $plugin['synonyms_behavior_service'] == $this->behaviorServiceId) {
        $options[$plugin_id] = $plugin['label'];
      }
    }

    $form['provider_plugin'] = array(
      '#type' => 'select',
      '#title' => $this->t('Synonyms provider'),
      '#description' => $this->t('Select what synonyms provider it should represent.'),
      '#required' => TRUE,
      '#options' => $options,
      '#default_value' => $this->entity->getProviderPlugin(),
      '#ajax' => [
        'wrapper' => 'synonyms-entity-configuration-ajax-wrapper',
        'event' => 'change',
        'callback' => [$class, 'ajaxForm'],
      ],
    );

    $form['ajax_wrapper'] = array(
      '#prefix' => '<div id="synonyms-entity-configuration-ajax-wrapper">',
      '#suffix' => '</div>',
    );

    $form['ajax_wrapper']['provider_configuration'] = array(
      '#tree' => TRUE,
      '#title' => $this->t('Provider settings'),
      '#open' => TRUE,
    );

    $form['ajax_wrapper']['behavior_configuration'] = array(
      '#tree' => TRUE,
      '#title' => $this->t('Behavior settings'),
      '#open' => TRUE,
    );

    if ($provider_plugin) {
      $provider_plugin_instance = $this->entity->getProviderPluginInstance();

      if ($provider_plugin_instance instanceof PluginFormInterface) {
        $form['ajax_wrapper']['provider_configuration']['#type'] = 'details';
        $form['ajax_wrapper']['provider_configuration'] += $provider_plugin_instance->buildConfigurationForm($form['ajax_wrapper']['provider_configuration'], $form_state);
      }

      $behavior_service_instance = $provider_plugin_instance->getBehaviorServiceInstance();
      if ($behavior_service_instance instanceof SynonymsBehaviorConfigurableInterface) {
        $form['ajax_wrapper']['behavior_configuration']['#type'] = 'details';
        $form['ajax_wrapper']['behavior_configuration'] += $behavior_service_instance->buildConfigurationForm($form['ajax_wrapper']['behavior_configuration'], $form_state, $this->entity->getBehaviorConfiguration(), $this->entity);
      }

    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    if ($this->entity->getProviderPluginInstance() instanceof PluginFormInterface) {
      $this->entity->getProviderPluginInstance()->validateConfigurationForm($form['ajax_wrapper']['provider_configuration'], $this->getSubFormState('provider_configuration', $form, $form_state));
    }

    if ($this->entity->getProviderPluginInstance()->getBehaviorServiceInstance() instanceof SynonymsBehaviorConfigurableInterface) {
      $this->entity->getProviderPluginInstance()->getBehaviorServiceInstance()->validateConfigurationForm($form['ajax_wrapper']['behavior_configuration'], $this->getSubFormState('behavior_configuration', $form, $form_state), $this->entity);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    if ($this->entity->getProviderPluginInstance() instanceof PluginFormInterface) {
      $this->entity->getProviderPluginInstance()->submitConfigurationForm($form['ajax_wrapper']['provider_configuration'], $this->getSubFormState('provider_configuration', $form, $form_state));
    }

    if ($this->entity->getProviderPluginInstance()->getBehaviorServiceInstance() instanceof SynonymsBehaviorConfigurableInterface) {
      $this->entity->setBehaviorConfiguration($this->entity->getProviderPluginInstance()->getBehaviorServiceInstance()->submitConfigurationForm($form['ajax_wrapper']['behavior_configuration'], $this->getSubFormState('behavior_configuration', $form, $form_state), $this->entity));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $status = $this->entity->save();

    if ($status) {
      drupal_set_message($this->t('Saved the %label synonym configuration.', array(
        '%label' => $this->entity->label(),
      )));
    }
    else {
      drupal_set_message($this->t('The %label synonym configuration was not saved.', array(
        '%label' => $this->entity->label(),
      )), 'error');
    }

    $form_state->setRedirect('entity.synonym.overview');
  }

  /**
   * Check whether entity with such ID already exists.
   *
   * @param string $id
   *   Entity ID to check
   *
   * @return bool
   *   Whether entity with such ID already exists.
   */
  public function exist($id) {
    $entity = $this->entityQuery->get('synonym')
      ->condition('id', $id)
      ->execute();
    return (bool) $entity;
  }

  /**
   * Ajax callback.
   */
  public static function ajaxForm(array &$form, FormStateInterface $form_state, Request $request) {
    return $form['ajax_wrapper'];
  }

  /**
   * Supportive method to create sub-form-states.
   *
   * @param string $element_name
   *   Name of the nested form element for which to create a sub form state
   * @param array $form
   *   Full form array
   * @param FormStateInterface $form_state
   *   Full form state out of which to create sub form state
   *
   * @return SubformState
   *   Sub form state object generated based on the input arguments
   */
  protected function getSubFormState($element_name, array $form, FormStateInterface $form_state) {
    return SubformState::createForSubform($form['ajax_wrapper'][$element_name], $form, $form_state);
  }

}
