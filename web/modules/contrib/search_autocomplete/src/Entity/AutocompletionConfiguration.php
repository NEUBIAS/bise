<?php

namespace Drupal\search_autocomplete\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\search_autocomplete\AutocompletionConfigurationInterface;
use Drupal\search_autocomplete\Suggestion;
use Drupal\search_autocomplete\SuggestionGroup;

/**
 * Defines the autocompletion_configuration entity.
 *
 * The lines below, starting with '@ConfigEntityType,' are a plugin annotation.
 * These define the entity type to the entity type manager.
 *
 * The properties in the annotation are as follows:
 *  - id: The machine name of the entity type.
 *  - label: The human-readable label of the entity type. We pass this through
 *    the "@Translation" wrapper so that the multilingual system may
 *    translate it in the user interface.
 *  - controllers: An array specifying controller classes that handle various
 *    aspects of the entity type's functionality. Below, we've specified
 *    controllers which can list, add, edit, and delete our
 *    autocompletion_configuration entity, and which control user access to
 *    these capabilities.
 *  - config_prefix: This tells the config system the prefix to use for
 *    filenames when storing entities. Because we don't specify, it will be
 *    the module's name. This means that the default entity we include in our
 *    module has the filename
 *    'search_autocomplete.autocompletion_configuration.xxx.yml'.
 *  - entity_keys: Specifies the class properties in which unique keys are
 *    stored for this entity type. Unique keys are properties which you know
 *    will be unique, and which the entity manager can use as unique in database
 *    queries.
 *
 * @see annotation
 * @see Drupal\Core\Annotation\Translation
 *
 * @ingroup search_autocomplete
 *
 * @ConfigEntityType(
 *   id = "autocompletion_configuration",
 *   label = @Translation("Autocompletion Configuration"),
 *   admin_permission = "administer search autocomplete",
 *   handlers = {
 *     "access" = "Drupal\search_autocomplete\AutocompletionConfigurationAccessControlHandler",
 *     "list_builder" = "Drupal\search_autocomplete\Controller\AutocompletionConfigurationListBuilder",
 *     "form" = {
 *       "add" = "Drupal\search_autocomplete\Form\AutocompletionConfigurationAddForm",
 *       "edit" = "Drupal\search_autocomplete\Form\AutocompletionConfigurationEditForm",
 *       "delete" = "Drupal\search_autocomplete\Form\AutocompletionConfigurationDeleteForm"
 *     }
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "selector" = "selector"
 *   },
 *   links = {
 *     "edit-form" =
 *       "/examples/search_autocomplete/manage/{autocompletion_configuration}",
 *     "delete-form" = "/examples/search_autocomplete/manage/{autocompletion_configuration}/delete"
 *   }
 * )
 */
class AutocompletionConfiguration extends ConfigEntityBase implements AutocompletionConfigurationInterface {

  /**
   * The autocompletion_configuration ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The autocompletion_configuration UUID.
   *
   * @var string
   */
  protected $uuid;

  /**
   * The autocompletion_configuration label.
   *
   * @var string
   */
  protected $label;

  /**
   * The field selector to apply the autocompletion on.
   *
   * @var string
   */
  protected $selector;

  /**
   * Define if the configuration is enabled,
   * ie if the autocompletion will occur.
   *
   * @var bool
   */
  protected $status;

  /**
   * Define how much characters needs to be entered in the field before
   * autocompletion occurs.
   *
   * @var int
   */
  protected $minChar;

  /**
   * Define how much suggestions should be displayed among matching suggestions
   * available.
   *
   * @var int
   */
  protected $maxSuggestions;

  /**
   * Define if form should be submitted when suggestion is chosen.
   *
   * @var boolean
   */
  protected $autoSubmit;

  /**
   * Define if user should be redirected to suggestion when it is chosen.
   *
   * @var boolean
   */
  protected $autoRedirect;

  /**
   * Define a label that should be displayed when no results are available.
   *
   * @var string
   */
  protected $noResultLabel;

  /**
   * Define a value entered when clicking the "no result" label.
   *
   * @var string
   */
  protected $noResultValue;

  /**
   * Define a link to be redirected to, wen "no result" is available.
   *
   * @var string
   */
  protected $noResultLink;


  /**
   * Define a label that should be displayed when more results then what can
   * be displayed are available.
   *
   * @var string
   */
  protected $moreResultsLabel;

  /**
   * Define a value entered when clicking the "more results" label.
   *
   * @var string
   */
  protected $moreResultsValue;

  /**
   * Define a link to be redirected to, wen "more results" links are available.
   *
   * @var string
   */
  protected $moreResultsLink;

  /**
   * Define the source of the completions.
   *
   * @var string
   */
  protected $source;

  /**
   * Define the theme to be used for autocompletion display.
   *
   * @var string
   */
  protected $theme;

  /**
   * Define if users can edit this configuration.
   *
   * @var string
   */
  protected $editable;

  /**
   * Define if users can delete this configuration.
   *
   * @var string
   */
  protected $deletable;


  /**
   * {@inheritdoc}
   */
  public function __construct(array $values, $entity_type_id = 'autocompletion_configuration') {
    parent::__construct($values, $entity_type_id);
  }


  /** ------------------------------ *
   *  ---------  GETTERS  ---------- */

  /**
   * {@inheritdoc}
   */
  public function getSelector() {
    return $this->selector;
  }

  /**
   * {@inheritdoc}
   */
  public function getStatus() {
    return $this->status;
  }

  /**
   * {@inheritdoc}
   */
  public function getMinChar() {
    return $this->minChar;
  }

  /**
   * {@inheritdoc}
   */
  public function getMaxSuggestions() {
    return $this->maxSuggestions;
  }

  /**
   * {@inheritdoc}
   */
  public function getAutoSubmit() {
    return $this->autoSubmit;
  }

  /**
   * {@inheritdoc}
   */
  public function getAutoRedirect() {
    return $this->autoRedirect;
  }

  /**
   * {@inheritdoc}
   */
  public function getNoResultLabel() {
    return $this->noResultLabel;
  }

  /**
   * {@inheritdoc}
   */
  public function getNoResultValue() {
    return $this->noResultValue;
  }

  /**
   * {@inheritdoc}
   */
  public function getNoResultLink() {
    return $this->noResultLink;
  }

  /**
   * {@inheritdoc}
   */
  public function getMoreResultsLabel() {
    return $this->moreResultsLabel;
  }

  /**
   * {@inheritdoc}
   */
  public function getMoreResultsValue() {
    return $this->moreResultsValue;
  }

  /**
   * {@inheritdoc}
   */
  public function getMoreResultsLink() {
    return $this->moreResultsLink;
  }

  /**
   * {@inheritdoc}
   */
  public function getSource() {
    return $this->source;
  }

  /**
   * {@inheritdoc}
   */
  public function getTheme() {
    return $this->theme;
  }

  /**
   * {@inheritdoc}
   */
  public function getEditable() {
    return $this->editable;
  }

  /**
   * {@inheritdoc}
   */
  public function getDeletable() {
    return $this->deletable;
  }

  /** ----------------------------- *
   *  ---------  SETTERS  --------- */

  /**
   * {@inheritdoc}
   */
  public function setSelector($selector) {
    $this->selector = $selector;
  }

  /**
   * {@inheritdoc}
   */
  public function setStatus($status) {
    $this->status = $status;
  }

  /**
   * {@inheritdoc}
   */
  public function setMinChar($min_char) {
    $this->minChar = $min_char;
  }

  /**
   * {@inheritdoc}
   */
  public function setMaxSuggestions($max_suggestions) {
    $this->maxSuggestions = $max_suggestions;
  }

  /**
   * {@inheritdoc}
   */
  public function setAutoSubmit($auto_submit) {
    $this->autoSubmit = $auto_submit;
  }

  /**
   * {@inheritdoc}
   */
  public function setAutoRedirect($auto_redirect) {
    $this->autoRedirect = $auto_redirect;
  }

  /**
   * {@inheritdoc}
   */
  public function setNoResultLabel($no_result_label) {
    $this->noResultLabel = $no_result_label;
  }

  /**
   * {@inheritdoc}
   */
  public function setNoResultValue($no_result_value) {
    $this->noResultValue = $no_result_value;
  }

  /**
   * {@inheritdoc}
   */
  public function setNoResultLink($no_result_link) {
    $this->noResultLink = $no_result_link;
  }

  /**
   * {@inheritdoc}
   */
  public function setMoreResultsLabel($more_results_label) {
    $this->moreResultsLabel = $more_results_label;
  }

  /**
   * {@inheritdoc}
   */
  public function setMoreResultsValue($more_results_value) {
    $this->moreResultsValue = $more_results_value;
  }

  /**
   * {@inheritdoc}
   */
  public function setMoreResultsLink($more_results_link) {
    $this->moreResultsLink = $more_results_link;
  }

  /**
   * {@inheritdoc}
   */
  public function setSource($source) {
    $this->source = $source;
  }

  /**
   * {@inheritdoc}
   */
  public function setTheme($theme) {
    $this->theme = $theme;
  }

  /**
   * {@inheritdoc}
   */
  public function setEditable($editable) {
    $this->editable = $editable;
  }

  /**
   * {@inheritdoc}
   */
  public function setDeletable($deletable) {
    $this->deletable = $deletable;
  }

}
