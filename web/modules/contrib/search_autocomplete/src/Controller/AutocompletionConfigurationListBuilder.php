<?php

namespace Drupal\search_autocomplete\Controller;

use Drupal\Component\Render\HtmlEscapedText;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a listing of autocompletion_configuration entities.
 *
 * List Controllers provide a list of entities in a tabular form. The base
 * class provides most of the rendering logic for us. The key functions
 * we need to override are buildHeader() and buildRow(). These control what
 * columns are displayed in the table, and how each row is displayed
 * respectively.
 *
 * Drupal locates the list controller by looking for the "list" entry under
 * "controllers" in our entity type's annotation. We define the path on which
 * the list may be accessed in our module's *.routing.yml file. The key entry
 * to look for is "_entity_list". In *.routing.yml, "_entity_list" specifies
 * an entity type ID. When a user navigates to the URL for that router item,
 * Drupal loads the annotation for that entity type. It looks for the "list"
 * entry under "controllers" for the class to load.
 *
 * @package Drupal\search_autocomplete\Controller
 *
 * @ingroup search_autocomplete
 */
class AutocompletionConfigurationListBuilder extends ConfigEntityListBuilder implements FormInterface {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'search_autocomplete_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['search_autocomplete.settings'];
  }


  /**
   * Implements \Drupal\Core\Form\FormInterface::buildForm().
   *
   * Form constructor for the main block administration form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $settings = \Drupal::config('search_autocomplete.settings');

    // Define the table of configurations.
    $form['configs'] = array(
      '#type' => 'table',
      '#header' => array(
        $this->t('Autocompletion Configuration Name'),
        $this->t('Enabled'),
        $this->t('Selector'),
        $this->t('Operations'),
      ),
    );

    // Retrieve existing configurations.
    $entity_ids = $this->getEntityIds();
    $entities = $this->storage->loadMultiple($entity_ids);

    // Build blocks first for each region.
    $configs = array();
    foreach ($entities as $entity_id => $entity) {
      $editable = $entity->getEditable() ? 'editable' : '';
      $deletable = $entity->getEditable() ? 'deletable' : '';
      $form['configs'][$entity_id]['#attributes'] = array('id' => array($entity_id), 'class' => array($editable, $deletable));
      $form['configs'][$entity_id]['label'] = array(
        '#markup' => new HtmlEscapedText($entity->label()),
      );
      $form['configs'][$entity_id]['enabled'] = array(
        '#type' => 'checkbox',
        '#default_value' => $entity->getStatus(),
      );
      $form['configs'][$entity_id]['selector'] = array(
        '#markup' => new HtmlEscapedText($entity->getSelector()),
      );
      $form['configs'][$entity_id]['operations'] = $this->buildOperations($entity);
    }

    // Use admin helper tool option settings.
    $form['admin_helper'] = array(
      '#type'           => 'checkbox',
      '#title'          => t('Use autocompletion helper tool for Search Autocomplete administrators.'),
      '#description'    => t('If enabled, user with "administer Search Autocomplete" permission will be able to use admin helper tool on input fields (recommended).'),
      '#default_value'  => $settings->get('admin_helper'),
    );

    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save changes'),
      '#button_type' => 'primary',
    );

    return $form;

  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::validateForm().
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // No validation.
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::submitForm().
   *
   * Form submission handler for the main block administration form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    // Save global configurations.
    \Drupal::configFactory()->getEditable('search_autocomplete.settings')
      ->set('admin_helper', $values['admin_helper'])
      ->save();

    // Save all configuration activations.
    $entities = $this->storage->loadMultiple(array_keys($form_state->getValue('configs')));
    foreach ($entities as $entity_id => $entity) {
      $entity_values = $form_state->getValue(array('configs', $entity_id));
      $entity->setStatus($entity_values['enabled']);
      $entity->save();
    }
    drupal_set_message($this->t('Data have been saved.'));
  }

  /**
   * Adds some descriptive text to our entity list.
   *
   * Typically, there's no need to override render(). You may wish to do so,
   * however, if you want to add markup before or after the table.
   *
   * @return array
   *   Renderable array.
   */
  public function render() {
    return \Drupal::formBuilder()->getForm($this);
  }

}
