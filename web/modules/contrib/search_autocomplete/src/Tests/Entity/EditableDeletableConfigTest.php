<?php

namespace Drupal\search_autocomplete\Tests\Entity;

use Drupal\search_autocomplete\Entity\AutocompletionConfiguration;
use Drupal\simpletest\WebTestBase;

/**
 * Test basic CRUD on configurations.
 *
 * @group Search Autocomplete
 *
 * @ingroup seach_auocomplete
 */
class EditableDeletableConfigTest extends WebTestBase {

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
      'name' => 'Manage Autocompletion Configuration test.',
      'description' => 'Test the access authorization for editable, deletable config.',
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
   * Check access authorizations over editable configurations.
   */
  public function testEditableEntity() {

    // Get config setup.
    $config_id = 'search_block';
    $config = AutocompletionConfiguration::load($config_id);

    // Verify that editable configuration can be edited on GUI.
    $this->drupalGet('/admin/config/search/search_autocomplete');
    $elements = $this->xpath('//tr[@id="' . $config_id . '"]//li[contains(@class, "edit")]');
    $this->assertTrue(isset($elements[0]), t('Editable config has Edit operation'));

    // Check access permission to edit page for editable configurations.
    $this->drupalGet('/admin/config/search/search_autocomplete/manage/' . $config_id);
    $this->assertResponse(200, "Editable configuration can be edited from GUI");

    // Remove editability for this configuration.
    $config = AutocompletionConfiguration::load('search_block');
    $config->setEditable(FALSE);
    $config->save();

    // Verify that none editable configuration cannot be edited on GUI.
    $this->drupalGet('/admin/config/search/search_autocomplete');
    $elements = $this->xpath('//tr[@id="' . $config_id . '"]//li[contains(@class, "edit")]');
    $this->assertFalse(isset($elements[0]), t('Editable config has Edit operation'));

    // Check that none editable configurations cannot be edited.
    $this->drupalGet('/admin/config/search/search_autocomplete/manage/' . $config_id);
    $this->assertResponse(403, "None editable configuration cannot be edited from GUI");
  }

  /**
   * Check access authorizations over deletable configurations.
   */
  public function testDeletableEntity() {

    // Get config setup.
    $config_id = 'search_block';
    $config = AutocompletionConfiguration::load($config_id);

    // Verify that default configuration search_block cannot be edited on GUI.
    $this->drupalGet('/admin/config/search/search_autocomplete');
    $elements = $this->xpath('//tr[@id="' . $config_id . '"]//li[contains(@class, "delete")]');
    $this->assertFalse(isset($elements[0]), t('Deletable config has Delete operation'));

    // Check access permission to delete page for none deletable configurations.
    $this->drupalGet('/admin/config/search/search_autocomplete/manage/' . $config_id . '/delete');
    $this->assertResponse(403, "None deletable configuration cannot be deleted from GUI");

    // Remove editability for this configuration.
    $config->setDeletable(TRUE);
    $config->save();

    // Verify that deletable configuration can be deleted from GUI.
    $this->drupalGet('/admin/config/search/search_autocomplete');
    $elements = $this->xpath('//tr[@id="' . $config_id . '"]//li[contains(@class, "delete")]');
    $this->assertTrue(isset($elements[0]), t('Deletable config has Delete operation'));

    // Check that deletable configurations can be deleted.
    $this->drupalGet('/admin/config/search/search_autocomplete/manage/' . $config_id . '/delete');
    $this->assertResponse(200, "Deletable configuration can be deleted from GUI");
  }


}
