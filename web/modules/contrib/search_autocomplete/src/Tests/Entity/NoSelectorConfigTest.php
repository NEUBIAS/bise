<?php

namespace Drupal\search_autocomplete\Tests\Entity;

use Drupal\simpletest\WebTestBase;

/**
 * Test special cases of configurations.
 *
 * @group Search Autocomplete
 *
 * @ingroup seach_auocomplete
 */
class NoSelectorConfigTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('node', 'search_autocomplete');

  public $adminUser;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Test Autocompletion Configuration test.',
      'description' => 'Test special autocompletion configurations scenario.',
      'group' => 'Search Autocomplete',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->adminUser = $this->drupalCreateUser(array('administer search autocomplete'));
    $this->drupalLogin($this->adminUser);
  }


  /**
   * Test addition with default values from URL.
   */
  public function testAdditionFromUrl() {

    // Add new from URL.
    $options = array(
      'query' => array(
        'label'     => 'test label',
        'selector'  => 'input#edit',
      ),
    );
    $this->drupalGet('admin/config/search/search_autocomplete/add', $options);

    $config_name = "testing_from_url";
    $config = array(
      'label'             => 'test label',
      'selector'          => 'input#edit',
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
    );

    // Check fields.
    $this->assertFieldByName('label', $config['label']);
    $this->assertFieldByName('selector', $config['selector']);

    // Click Add new button.
    $this->drupalPostForm(
      NULL,
      array(
        'label' => $config['label'],
        'id' => $config_name,
        'selector' => $config['selector'],
      ),
      t('Create Autocompletion Configuration')
    );

    // ----------------------------------------------------------------------
    // 2) Verify that add redirect to edit page.
    $this->assertUrl('/admin/config/search/search_autocomplete/manage/' . $config_name);

    // ----------------------------------------------------------------------
    // 3) Verify that default add configuration values are inserted.
    $this->assertFieldByName('label', $config['label']);
    $this->assertFieldByName('selector', $config['selector']);
    $this->assertFieldByName('minChar', $config['minChar']);
    $this->assertFieldByName('maxSuggestions', $config['maxSuggestions']);
    $this->assertFieldByName('autoSubmit', $config['autoSubmit']);
    $this->assertFieldByName('autoRedirect', $config['autoRedirect']);
    $this->assertFieldByName('noResultLabel', $config['noResultLabel']);
    $this->assertFieldByName('noResultValue', $config['noResultValue']);
    $this->assertFieldByName('noResultLink', $config['noResultLink']);
    $this->assertFieldByName('moreResultsLabel', $config['moreResultsLabel']);
    $this->assertFieldByName('moreResultsValue', $config['moreResultsValue']);
    $this->assertFieldByName('moreResultsLink', $config['moreResultsLink']);
    $this->assertFieldByName('source', $config['source']);
    $this->assertOptionSelected('edit-theme', $config['theme']);

  }

}
