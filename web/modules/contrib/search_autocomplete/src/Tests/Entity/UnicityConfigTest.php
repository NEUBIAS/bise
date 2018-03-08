<?php

namespace Drupal\search_autocomplete\Tests\Entity;

use Drupal\simpletest\WebTestBase;

/**
 * Test uniticity when creation configurations.
 *
 * @group Search Autocomplete
 *
 * @ingroup seach_auocomplete
 */
class UnicityConfigTest extends WebTestBase {

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
      'description' => 'Test unicity autocompletion configurations scenario.',
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
   * Configuration creation should fail if ID is not unique.
   */
  public function testUniqueId() {

    // ----------------------------------------------------------------------
    // 1) Create the configuration.
    // Add new configurations.
    $this->drupalGet('admin/config/search/search_autocomplete/add');

    // Default values.
    $config_name = "testing";
    $config = array(
      'label'             => 'test-label',
      'selector'          => 'input#edit',
    );

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
    // 2) Create the configuration again.
    // Add new configurations.
    $this->drupalGet('admin/config/search/search_autocomplete/add');

    // Default values.
    $config_name = "testing";
    $config = array(
      'label'             => 'test-another',
      'selector'          => 'another',
    );

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
    $this->assertRaw(t('The machine-readable name is already in use. It must be unique.'));
  }

  /**
   * Configuration creation should fail if selector is not unique.
   */
  public function testUniqueSelector() {

    // ----------------------------------------------------------------------
    // 1) Create the configuration.
    // Add new configurations.
    $this->drupalGet('admin/config/search/search_autocomplete/add');

    // Default values.
    $config_name = "test1";
    $config = array(
      'label'             => 'test1',
      'selector'          => 'input#edit',
    );

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
    // 2) Create the configuration again.
    // Add new configurations.
    $this->drupalGet('admin/config/search/search_autocomplete/add');

    // Default values.
    $config_name = "test2";
    $config = array(
      'label'             => 'test2',
      'selector'          => 'input#edit',
    );

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
    $this->assertRaw(t('The selector ID must be unique.'));
  }

}
