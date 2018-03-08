<?php

namespace Drupal\synonyms\SynonymsService\Behavior;

use Drupal\Core\Form\FormStateInterface;
use Drupal\synonyms\SynonymInterface;

/**
 * Interface of a configurable synonyms behavior.
 */
interface SynonymsBehaviorConfigurableInterface extends SynonymsBehaviorInterface {

  /**
   * Build configuration form.
   *
   * @param array $form
   *   Form into which your configuration form will be embedded. You are
   *   supposed to extend this array with additional configuration form elements
   *   that your behavior needs
   * @param FormStateInterface $form_state
   *   Form state object that corresponds to this form
   * @param array $configuration
   *   Array of existing configuration for your behavior. Normally you would use
   *   it as a source of default values for your configuration form elements
   * @param SynonymInterface $synonym_config
   *   Synonym config entity in the context of which the form is being built
   *
   * @return array
   *   Extended $form that includes the form elements required for configuration
   *   of your behavior
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, array $configuration, SynonymInterface $synonym_config);

  /**
   * Validate submitted values into your configuration form.
   *
   * @param array $form
   *   Your configuration form as it was built in
   *   static::buildConfigurationForm()
   * @param FormStateInterface $form_state
   *   Form state that corresponds to this form. You should rise form validation
   *   errors on this form state, should you discover any in user input
   * @param SynonymInterface $synonym_config
   *   Synonym config entity in the context of which the form is being built
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state, SynonymInterface $synonym_config);

  /**
   * Process submitted values and generate new configuration.
   *
   * @param array $form
   *   Your configuration form as it was built in
   *   static::buildConfigurationForm()
   * @param FormStateInterface $form_state
   *   Form state that corresponds to this form
   * @param SynonymInterface $synonym_config
   *   Synonym config entity in the context of which the form is being built
   *
   * @return array
   *   Array of new behavior configuration that corresponds to the submitted
   *   values in the form
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state, SynonymInterface $synonym_config);

}
