<?php

namespace Drupal\search_autocomplete\Tests\Entity;

use Drupal\search_autocomplete\Entity\AutocompletionConfiguration;
use Drupal\simpletest\WebTestBase;

/**
 * Test default configurations.
 *
 * @group Search Autocomplete
 *
 * @ingroup seach_auocomplete
 */
class DefaultConfigTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('node', 'search_autocomplete');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Default Entity inclusion tests.',
      'description' => 'Test the inclusion of default configurations.',
      'group' => 'Search Autocomplete',
    );
  }

  /**
   * Check that default entities are properly included.
   *
   * 1) check for search_block default configuration.
   */
  public function testDefaultConfigEntityInclusion() {

    // Build a configuration data.
    $config = array(
      'id'                => 'search_block',
      'label'             => 'Search Block',
      'selector'          => '',
      'status'            => TRUE,
      'minChar'           => 3,
      'maxSuggestions'    => 10,
      'autoSubmit'        => TRUE,
      'autoRedirect'      => TRUE,
      'noResultLabel'     => 'No results found for [search-phrase]. Click to perform full search.',
      'noResultValue'     => '[search-phrase]',
      'noResultLink'      => '',
      'moreResultsLabel'  => 'View all results for [search-phrase].',
      'moreResultsValue'  => '[search-phrase]',
      'moreResultsLink'   => '',
      'source'            => 'autocompletion_callbacks_nodes::nodes_autocompletion_callback',
      'theme'             => 'basic-blue.css',
      'editable'          => TRUE,
      'deletable'         => FALSE,
    );

    // ----------------------------------------------------------------------
    // 1) Verify that the search_block default config is properly added.
    $entity = AutocompletionConfiguration::load($config['id']);
    $this->assertNotNull($entity, 'Default configuration search_block created during installation process.');

    $this->assertEqual($entity->id(), $config['id']);
    $this->assertEqual($entity->label(), $config['label']);
    $this->assertEqual($entity->getStatus(), $config['status']);
    $this->assertEqual($entity->getSelector(), $config['selector']);
    $this->assertEqual($entity->getMinChar(), $config['minChar']);
    $this->assertEqual($entity->getMaxSuggestions(), $config['maxSuggestions']);
    $this->assertEqual($entity->getAutoSubmit(), $config['autoSubmit']);
    $this->assertEqual($entity->getAutoRedirect(), $config['autoRedirect']);
    $this->assertEqual($entity->getNoResultLabel(), $config['noResultLabel']);
    $this->assertEqual($entity->getNoResultValue(), $config['noResultValue']);
    $this->assertEqual($entity->getNoResultLink(), $config['noResultLink']);
    $this->assertEqual($entity->getMoreResultsLabel(), $config['moreResultsLabel']);
    $this->assertEqual($entity->getMoreResultsValue(), $config['moreResultsValue']);
    $this->assertEqual($entity->getMoreResultsLink(), $config['moreResultsLink']);
    $this->assertEqual($entity->getSource(), $config['source']);
    $this->assertEqual($entity->getTheme(), $config['theme']);
    $this->assertEqual($entity->getEditable(), $config['editable']);
    $this->assertEqual($entity->getDeletable(), $config['deletable']);
  }

}
