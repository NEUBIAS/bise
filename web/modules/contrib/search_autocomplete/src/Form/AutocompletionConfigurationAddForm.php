<?php

namespace Drupal\search_autocomplete\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class AutocompletionConfigurationAddForm.
 *
 * Provides the add form for our autocompletion_configuration entity.
 *
 * @package Drupal\search_autocomplete\Form
 *
 * @ingroup search_autocomplete
 */
class AutocompletionConfigurationAddForm extends AutocompletionConfigurationFormBase {

  /**
   * Returns the actions provided by this form.
   *
   * For our add form, we only need to change the text of the submit button.
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
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Create Autocompletion Configuration');
    return $actions;
  }


  /**
   * Overrides Drupal\Core\Entity\EntityFormController::form() and
   * Drupal\search_autocomplete\Form\AutocompletionConfigurationFormBase::form()
   *
   * Builds the entity add form.
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

    // Get default selector from URL if available.
    $selector = '';
    if (isset($_REQUEST['selector'])) {
      $selector = urldecode($_REQUEST['selector']);
    }

    // Selector.
    $form['selector'] = array(
      '#type'           => 'textfield',
      '#title'          => $this->t('ID selector this configuration should apply to'),
      '#description'    => 'Enter a valid query selector for this configuration. This should be an ID or a class targeting an input field.',
      '#default_value'  => $selector ? $selector : $this->entity->getSelector(),
    );

    // Return the form.
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);

    // Add default values on the entity.
    $this->entity->setStatus(TRUE);
    $this->entity->setMinChar(3);
    $this->entity->setMaxSuggestions(10);
    $this->entity->setAutoSubmit(TRUE);
    $this->entity->setAutoRedirect(TRUE);
    $this->entity->setMoreResultsLabel($this->t('View all results for [search-phrase].'));
    $this->entity->setMoreResultsValue($this->t('[search-phrase]'));
    $this->entity->setMoreResultsLink('');
    $this->entity->setNoResultLabel($this->t('No results found for [search-phrase]. Click to perform full search.'));
    $this->entity->setNoResultValue($this->t('[search-phrase]'));
    $this->entity->setNoResultLink('');
    $this->entity->setSource('autocompletion_callbacks_nodes::nodes_autocompletion_callback');
    $this->entity->setTheme('basic-blue.css');
    $this->entity->setEditable(TRUE);
    $this->entity->setDeletable(TRUE);
    $this->entity->save();

    // Redirect to edit form once entity is added.
    $form_state->setRedirectUrl($this->entity->toUrl('edit-form'));
  }

}
