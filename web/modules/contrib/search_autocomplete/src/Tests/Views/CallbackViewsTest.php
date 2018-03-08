<?php

namespace Drupal\search_autocomplete\Tests\Views;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Utility\Html;
use Drupal\node\Entity\Node;
use Drupal\views\Tests\ViewTestBase;

/**
 * Test callback view configurations.
 *
 * @group Search Autocomplete
 *
 * @ingroup seach_auocomplete
 */
class CallbackViewsTest extends ViewTestBase {
  // Temporary fix suggested here: https://www.drupal.org/node/2391795
  protected $strictConfigSchema = FALSE;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('search', 'image', 'user', 'node', 'views_ui', 'search_autocomplete');

  /**
   * The admin user
   * @var \Drupal\user\Entity\User
   */
  public $adminUser;

  /**
   * The entity storage for nodes.
   *
   * @var \Drupal\node\NodeStorage
   */
  protected $nodeStorage;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Callback view configurationt tests.',
      'description' => 'Tests the callback view display.',
      'group' => 'Search Autocomplete',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Log with admin permissions.
    $this->adminUser = $this->drupalCreateUser(array('access content', 'administer views', 'administer search autocomplete'));
    $this->drupalLogin($this->adminUser);

    // Get the node manager.
    $this->nodeStorage = $this->container->get('entity.manager')
    ->getStorage('node');
  }

  /**
   * Basic checks : default views are inserted.
   */
  public function testDefaultView() {
    // Find the views in the view list page.
    $this->drupalGet("admin/structure/views");

    // Nodes callback view.
    $this->assertRaw(t('Nodes Autocompletion Callbacks'));
    $this->assertRaw(t('autocompletion_callbacks_nodes'));

    // Words callback view.
    $this->assertRaw(t('Words Autocompletion Callbacks'));
    $this->assertRaw(t('autocompletion_callbacks_words'));
  }

  /**
   * Default view content checks.
   */
  public function testDefaultViewContent() {

    // Retrieve node default view.
    $actual_json = $this->drupalGet("callback/nodes");
    $expected = array();
    $this->assertIdentical($actual_json, json_encode($expected), 'The expected JSON output was found.');

    // Create some published nodes of type article and page.
    $this->createNodes(5, "article", $expected);
    $this->createNodes(5, "page", $expected);

    // Log out user.
    $this->drupalLogout();

    // Get the view page as anonymous user.
    $actual_json = $this->drupalGet("callback/nodes");

    // Check the view result using serializer service.
    $expected_string = json_encode($expected);
    $this->assertIdentical($actual_json, $expected_string);

    // Re-test as anonymous user.
    $actual_json = $this->drupalGet("callback/nodes");
    $this->assertIdentical($actual_json, $expected_string);
  }

  /**
   * Helper methods: creates a given number of nodes of a give type.
   *
   * @param integer $number
   *   number of nodes to create.
   * @param string $type
   *   the type machine name of nodes to create.
   *
   * @return array
   *   the array of node results as it should be in the view result.
   */
  protected function createNodes($number, $type, &$expected) {
    $type = $this->drupalCreateContentType(['type' => $type, 'name' => $type]);
    for ($i = 1; $i < $number; $i++) {
      $settings = array(
        'body'      => array(array(
          'value' => $this->randomMachineName(32),
          'format' => filter_default_format(),
        ),
        ),
        'type'    => $type->id(),
        'created' => 123456789,
        'title'   => $type->id() . ' ' . $i,
        'status'  => TRUE,
        'promote' => rand(0, 1) == 1,
        'sticky'  => rand(0, 1) == 1,
        'uid'       => \Drupal::currentUser()->id(),
      );
      $node = Node::create($settings);
      $status = $node->save();
      $this->assertEqual($status, SAVED_NEW, new FormattableMarkup('Created node %title.', array('%title' => $node->label())));

      $result = array(
        'value' => $type->id() . ' ' . $i,
        'fields'  => array(
          'title'   => $type->id() . ' ' . $i,
          'created' => 'by ' . $this->adminUser->getUsername() . ' | Thu, 11/29/1973 - 21:33',
        ),
        'link'  => $node->toUrl('canonical', array('absolute' => TRUE))->toString(),
      );
      if ($i == 1) {
        $result += array(
          'group' => array(
            'group_id' => strtolower(Html::cleanCssIdentifier($type->label())),
            'group_name' => $type->label() . "s",
          ),
        );
      }
      $expected[] = $result;
    }
    return $expected;
  }

}
