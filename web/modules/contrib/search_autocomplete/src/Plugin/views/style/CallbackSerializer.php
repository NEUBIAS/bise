<?php

namespace Drupal\search_autocomplete\Plugin\views\style;

use Drupal\Component\Render\HtmlEscapedText;
use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\style\StylePluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The style plugin for serialized output formats.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "callback_serializer",
 *   title = @Translation("Callback Serializer"),
 *   help = @Translation("Serializes views row data using json_encode."),
 *   display_types = {"autocompletion_callback"}
 * )
 */
class CallbackSerializer extends StylePluginBase {

  /**
   * Overrides \Drupal\views\Plugin\views\style\StylePluginBase::$usesRowPlugin.
   */
  protected $usesRowPlugin = TRUE;

  /**
   * Overrides Drupal\views\Plugin\views\style\StylePluginBase::$usesFields.
   */
  protected $usesGrouping = TRUE;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * Constructs a Plugin object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->definition = $plugin_definition + $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    // Unset unecessary configurations.
    unset($form['grouping']['0']['rendered']);
    unset($form['grouping']['0']['rendered_strip']);
    unset($form['grouping']['0']['rendered_strip']);
    unset($form['grouping']['1']);

    // Add custom options.
    $field_labels = $this->displayHandler->getFieldLabels(TRUE);

    // Build the input field option.
    $input_label_descr = (empty($field_labels) ? '<b>' . t('Warning') . ': </b> ' . t('Requires at least one field in the view.') . '<br/>' : '') . t('Select the autocompletion input value. If the autocompletion settings are set to auto-submit, this value will be submitted as the suggestion is selected.');
    $form['input_label'] = array(
      '#title'          => t('Input Label'),
      '#type'           => 'select',
      '#description'    => new HtmlEscapedText($input_label_descr),
      '#default_value'  => $this->options['input_label'],
      '#disabled'       => empty($field_labels),
      '#required'       => TRUE,
      '#options'        => $field_labels,
    );

    // Build the link field option.
    $input_link_descr = (empty($field_labels) ? '<b>' . t('Warning') . ': </b> ' . t('Requires at least one field in the view.') . '<br/>' : '') . t('Select the autocompletion input link. If the autocompletion settings are set to auto-redirect, this link is where the user will be redirected as the suggestion is selected.');
    $form['input_link'] = array(
      '#title'          => t('Input Link'),
      '#type'           => 'select',
      '#description'    => new HtmlEscapedText($input_link_descr),
      '#default_value'  => $this->options['input_link'],
      '#disabled'       => empty($field_labels),
      '#required'       => TRUE,
      '#options'        => $field_labels,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function render() {

    // Group the rows according to the grouping instructions, if specified.
    $groups = $this->renderGrouping(
        $this->view->result,
        $this->options['grouping'],
        TRUE
    );

    return json_encode($groups);
  }

  /**
   * {@inheritdoc}
   */
  public function renderGrouping($records, $groupings = array(), $group_rendered = NULL) {

    $rows = array();
    $groups = array();

    // Iterate through all records for transformation.
    foreach ($records as $index => $row) {

      $this->view->rowPlugin->setRowOptions($this->options);

      // Render the row according to our custom needs.
      if (!isset($row->row_index) || $row->row_index == NULL) {
        $row->row_index = $index;
      }
      $rendered_row = $this->view->rowPlugin->render($row);

      // Case when it takes grouping.
      if ($groupings) {

        // Iterate through configured grouping field. Currently only one level
        // of grouping allowed.
        foreach ($groupings as $info) {

          $group_field_name = $info['field'];
          $group_id = '';
          $group_name = '';

          // Extract group data if available.
          if (isset($this->view->field[$group_field_name])) {
            // Extract group_id and transform it to machine name.
            $group_id = strtolower(Html::cleanCssIdentifier($this->getField($index, $group_field_name)));
            // Extract group displayed value.
            $group_name = $this->getField($index, $group_field_name) . 's';
          }

          // Create the group if it does not exist yet.
          if (empty($groups[$group_id])) {
            $groups[$group_id]['group']['group_id'] = $group_id;
            $groups[$group_id]['group']['group_name'] = $group_name;
            $groups[$group_id]['rows'] = array();
          }

          // Move the set reference into the row set of the group
          // we just determined.
          $rows = &$groups[$group_id]['rows'];
        }
      }
      else {
        // Create the group if it does not exist yet.
        if (empty($groups[''])) {
          $groups['']['group'] = '';
          $groups['']['rows'] = array();
        }
        $rows = &$groups['']['rows'];
      }
      // Add the row to the hierarchically positioned
      // row set we just determined.
      $rows[] = $rendered_row;
    }

    /*
     * Build the result from previous array.
     * @todo: find a more straight forward way to make it.
     */
    $return = array();
    foreach ($groups as $group_id => $group) {
      // Add group info on first row lign.
      if (isset($group['rows']) && isset($group['rows'][0])) {
        $group['rows'][0]['group'] = $group['group'];
      }
      // Add rows of this group to result.
      $return = array_merge($return, $group['rows']);
    }
    return $return;
  }
}
