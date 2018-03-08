<?php

namespace Drupal\search_autocomplete\Tests\Entity;

use Drupal\simpletest\WebTestBase;

/**
 * Test basic CRUD on configurations.
 *
 * @group Search Autocomplete
 *
 * @ingroup seach_auocomplete
 */
class BasicCRUDConfigTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('block', 'node', 'search_autocomplete');

  /**
   * Stores a user admin.
   * @var \Drupal\user\Entity\User
   */
  public $adminUser;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Manage Autocompletion Configuration test.',
      'description' => 'Test is autocompletion configurations can be added/edited/deleted.',
      'group' => 'Search Autocomplete',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    // Create admin user.
    $this->adminUser = $this->drupalCreateUser(array('administer search autocomplete'));
    // Log user as admin.
    $this->drupalLogin($this->adminUser);
    // Place the local_actions_block in content.
    $this->drupalPlaceBlock('local_actions_block', array('region' => 'content'));
  }


  /**
   * Check that autocompletion configurations can be added/edited/deleted.
   *
   * 1) Verify that we can add new configuration through admin UI.
   *
   * 2) Verify that add redirect to edit page.
   *
   * 3) Verify that default add configuration values are inserted.
   *
   * 4) Verify that user is redirected to listing page.
   *
   * 5) Verify that we can edit the configuration through admin UI.
   */
  public function testManageConfigEntity() {

    // ----------------------------------------------------------------------
    // 1) Verify that we can add new configuration through admin UI.
    // We  have the admin user logged in (through test setup), so we'll create
    // a new configuration.
    $this->drupalGet('/admin/config/search/search_autocomplete');

    // Check that action link is now there and click 'Add new' button.
    $this->clickLink('Add new Autocompletion Configuration');

    // Build a configuration data.
    $config_name = 'testing_config';
    $config = array(
      'label'             => 'Unit testing configuration',
      'selector'          => '#test-key',
      'minChar'           => '3',
      'maxSuggestions'    => '10',
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

    // Change default values.
    $config['minChar'] = 1;
    $config['noResultLabel'] = 'No result test label.';
    $config['autoRedirect'] = FALSE;
    $config['moreResultsLink'] = 'http://google.com';
    $config['source'] = '/user/' . $this->adminUser->id();

    $this->drupalPostForm(
      NULL,
      $config,
      t('Update')
    );

    // ----------------------------------------------------------------------
    // 4) Verify that user is redirected to listing page.
    $this->assertUrl('/admin/config/search/search_autocomplete');

    // ----------------------------------------------------------------------
    // 5) Verify that we can edit the configuration through admin UI.
    $this->drupalGet('/admin/config/search/search_autocomplete/manage/' . $config_name);
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

  /**
   * Check that none selector autocompletion configurations can be
   * added/edited/deleted.
   *
   * 1) Verify that we can add new configuration through admin UI.
   *
   * 2) Verify that add redirect to edit page.
   *
   * 3) Verify that default add configuration values are inserted.
   *
   * 4) Verify that user is redirected to listing page.
   *
   * 5) Verify that we can edit the configuration through admin UI.
   */
  public function testManageNoSelectorConfigEntity() {

    // ----------------------------------------------------------------------
    // 1) Verify that we can add new configuration through admin UI.
    // We  have the admin user logged in (through test setup), so we'll create
    // a new configuration.
    $this->drupalGet('/admin/config/search/search_autocomplete');

    // Click Add new button.
    $this->clickLink('Add new Autocompletion Configuration');

    // Build a configuration data.
    $config_name = 'testing_config';
    $config = array(
      'label'             => 'Unit testing configuration',
      'selector'          => '',
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

    // Change default values.
    $config['minChar'] = 1;
    $config['noResultLabel'] = 'No result test label.';
    $config['autoRedirect'] = FALSE;
    $config['moreResultsLink'] = 'http://google.com';
    $config['source'] = '/user/' . $this->adminUser->id();

    $this->drupalPostForm(
      NULL,
      $config,
      t('Update')
    );

    // ----------------------------------------------------------------------
    // 4) Verify that user is redirected to listing page.
    $this->assertUrl('/admin/config/search/search_autocomplete');
    $this->assertRaw("<td>Unit testing configuration</td>");

    // ----------------------------------------------------------------------
    // 5) Verify that we can edit the configuration through admin UI.
    $this->drupalGet('/admin/config/search/search_autocomplete/manage/' . $config_name);
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

    // ----------------------------------------------------------------------
    // 6) Verify that we can delete the configuration.
    $this->drupalGet("/admin/config/search/search_autocomplete/manage/" . $config_name . "/delete");
    $this->assertText('This action cannot be undone.');
    $this->drupalPostForm(NULL, array(), t('Delete this configuration'));
    $this->assertRaw('The autocompletion configuration <em class="placeholder">' . $config['label'] . '</em> is deleted.');
    $this->assertNoRaw("<td>Unit testing configuration</td>");
  }
}
