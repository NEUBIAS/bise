<?php

namespace Drupal\search_autocomplete\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AutocompletionConfigurationFormBase.
 *
 * Typically, we need to build the same form for both adding a new entity,
 * and editing an existing entity. Instead of duplicating our form code,
 * we create a base class. Drupal never routes to this class directly,
 * but instead through the child classes of AutocompletionConfigurationAddForm
 * and AutocompletionConfigurationEditForm.
 *
 * @package Drupal\search_autocomplete\Form
 *
 * @ingroup search_autocomplete
 */
class AutocompletionConfigurationFormBase extends EntityForm {

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityStorage;

  /**
   * Construct the AutocompletionConfigurationFormBase.
   *
   * For simple entity forms, there's no need for a constructor. Our
   * autocompletion_configuration form base, however, requires an entity query
   * factory to be injected into it from the container. We later use this query
   * factory to build an entity query for the exists() method.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   An entity query factory for the autocompletion_configuration entity type.
   */
  public function __construct(EntityStorageInterface $entity_storage) {
    $this->entityStorage = $entity_storage;
  }

  /**
   * Factory method for AutocompletionConfigurationFormBase.
   *
   * When Drupal builds this class it does not call the constructor directly.
   * Instead, it relies on this method to build the new object. Why? The class
   * constructor may take multiple arguments that are unknown to Drupal. The
   * create() method always takes one parameter -- the container. The purpose
   * of the create() method is twofold: It provides a standard way for Drupal
   * to construct the object, meanwhile it provides you a place to get needed
   * constructor parameters from the container.
   *
   * In this case, we ask the container for an entity query factory. We then
   * pass the factory to our class as a constructor parameter.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')->getStorage('autocompletion_configuration')
    );
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::form().
   *
   * Builds the entity add/edit form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param array $form_state
   *   An associative array containing the current state of the form.
   *
   * @return array
   *   An associative array containing the autocompletion_configuration
   *   add/edit form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get anything we need from the base class.
    $form = parent::buildForm($form, $form_state);

    // Drupal provides the entity to us as a class variable. If this is an
    // existing entity, it will be populated with existing values as class
    // variables. If this is a new entity, it will be a new object with the
    // class of our entity. Drupal knows which class to call from the
    // annotation on our AutocompletionConfiguration class.
    $autocompletion_configuration = $this->entity;

    // Get default label from URL if available.
    $label = '';
    if (isset($_REQUEST['label'])) {
      $label = urldecode($_REQUEST['label']);
    }

    // Label.
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Human readable name'),
      '#maxlength' => 255,
      '#description'    => 'Please enter a label for this autocompletion configuration.',
      '#default_value' => $label ? $label : $autocompletion_configuration->label(),
      '#required' => TRUE,
    );

    // ID.
    $form['id'] = array(
      '#type' => 'machine_name',
      '#title' => $this->t('Machine name'),
      '#default_value' => $autocompletion_configuration->id(),
      '#machine_name' => array(
        'exists' => array($this->entityStorage, 'load'),
        'replace_pattern' => '([^a-z0-9_]+)|(^custom$)',
        'error' => 'The machine-readable name must be unique, and can only contain lowercase letters, numbers, and underscores. Additionally, it can not be the reserved word "custom".',
      ),
      '#disabled' => !$autocompletion_configuration->isNew(),
    );

    // Return the form.
    return $form;
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::actions().
   *
   * To set the submit button text, we need to override actions().
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   An associative array containing the current state of the form.
   *
   * @return array
   *   An array of supported actions for the current entity form.
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    // Get the basic actins from the base class.
    $actions = parent::actions($form, $form_state);

    // Change the submit button text.
    $actions['submit']['#value'] = $this->t('Save');

    // Return the result.
    return $actions;
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::validate().
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   An associative array containing the current state of the form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // Retrieve all configurations with same selector.
    $entities = $this->entityStorage->loadByProperties(
      array(
        'selector'  => $form_state->getValue('selector'),
      )
    );

    // If other configurations have the same selector (not null)...
    if ($entities != NULL && $form_state->getValue('selector')) {
      // Exclude that same entity (case of update).
      if (count($entities) == 1 && isset($entities[$this->entity->id()])) {
        return;
      }
      // Otherwise notify the error.
      else {
        $form_state->setErrorByName('selector', $this->t('The selector ID must be unique.'));
      }
    }

  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::save().
   *
   * Saves the entity. This is called after submit() has built the entity from
   * the form values. Do not override submit() as save() is the preferred
   * method for entity form controllers.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   An associative array containing the current state of the form.
   */
  public function save(array $form, FormStateInterface $form_state) {
    // EntityForm provides us with the entity we're working on.
    $autocompletion_configuration = $this->entity;

    // Drupal already populated the form values in the entity object. Each
    // form field was saved as a public variable in the entity class. PHP
    // allows Drupal to do this even if the method is not defined ahead of
    // time.
    $status = $autocompletion_configuration->save();

    // Grab the URL of the new entity. We'll use it in the message.
    $url = $autocompletion_configuration->toUrl();

    if ($status == SAVED_UPDATED) {
      // If we edited an existing entity...
      drupal_set_message($this->t('Autocompletion Configuration %label has been updated.', array('%label' => $autocompletion_configuration->label())));
      $this->logger('search_autocomplete')->notice('Autocompletion Configuration %label has been updated.', ['%label' => $autocompletion_configuration->label()]);
    }
    else {
      // If we created a new entity...
      drupal_set_message($this->t('Autocompletion Configuration %label has been added.', array('%label' => $autocompletion_configuration->label())));
      $this->logger('search_autocomplete')->notice('Autocompletion Configuration %label has been added.', ['%label' => $autocompletion_configuration->label()]);
    }

    // Redirect the user back to the listing route after the save operation.
    $form_state->setRedirect('autocompletion_configuration.list');
  }

}
