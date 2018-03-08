<?php

namespace Drupal\Tests\views_bulk_operations\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\node\Entity\NodeType;
use Drupal\user\Entity\User;
use Drupal\views\Views;
use Drupal\language\Entity\ConfigurableLanguage;

/**
 * @coversDefaultClass \Drupal\views_bulk_operations\Service\ViewsBulkOperationsViewData
 * @group views_bulk_operations
 */
class ViewsBulkOperationsDataServiceTest extends KernelTestBase {

  use NodeCreationTrait {
    getNodeByTitle as drupalGetNodeByTitle;
    createNode as drupalCreateNode;
  }

  /**
   * Test nodes data.
   *
   * @var array
   */
  protected $testNodesData;

  /**
   * The expected total number of view results.
   *
   * @var int
   */
  protected $resultsCount = 0;

  /**
   * The tested service.
   *
   * @var \Drupal\views_bulk_operations\Service\ViewsbulkOperationsViewData
   */
  protected $vboDataService;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'user',
    'node',
    'field',
    'content_translation',
    'views_bulk_operations',
    'views_bulk_operations_test',
    'views',
    'filter',
    'language',
    'text',
    'action',
    'system',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installSchema('node', 'node_access');
    $this->installSchema('system', 'sequences');
    $this->installSchema('system', 'key_value_expire');

    $user = User::create();
    $user->setPassword('password');
    $user->enforceIsNew();
    $user->setEmail('email');
    $user->setUsername('user_name');
    $user->save();
    user_login_finalize($user);

    $this->installConfig([
      'system',
      'filter',
      'views_bulk_operations_test',
      'language',
    ]);

    $languages = ['pl', 'es', 'it', 'fr', 'de'];
    $count_languages = count($languages);
    for ($i = 0; $i < $count_languages; $i++) {
      $language = ConfigurableLanguage::createFromLangcode($languages[$i]);
      $language->save();
    }

    $type = NodeType::create([
      'type' => 'page',
      'name' => 'page',
    ]);
    $type->save();

    \Drupal::service('content_translation.manager')->setEnabled('node', 'page', TRUE);
    \Drupal::entityManager()->clearCachedDefinitions();

    // Create some test nodes with translations.
    $this->testNodesData = [];
    $time = REQUEST_TIME;
    for ($i = 0; $i < 10; $i++) {
      $time -= $i;
      $title = 'Title ' . $i;
      $node = $this->drupalCreateNode([
        'type' => 'page',
        'title' => $title,
        'sticky' => FALSE,
        'created' => $time,
        'changed' => $time,
      ]);
      $this->testNodesData[$node->id()]['en'] = $title;
      $this->resultsCount++;

      $langcode = $languages[rand(0, $count_languages - 1)];
      $title = 'Translated title ' . $langcode . ' ' . $i;
      $translation = $node->addTranslation($langcode, [
        'title' => $title,
      ]);
      $translation->save();
      $this->testNodesData[$node->id()][$langcode] = $title;
      $this->resultsCount++;
    }

    // Initialize the tested service.
    $this->vboDataService = $this->container->get('views_bulk_operations.data');
  }

  /**
   * Tests the getEntityDefault() method.
   *
   * @covers ::getEntityDefault
   */
  public function testViewsbulkOperationsViewDataEntityGetter() {
    $this->assertTrue(TRUE);
    // Initialize and execute the test view with all items displayed.
    $view = Views::getView('views_bulk_operations_test');
    $view->setDisplay('page_1');
    $view->setItemsPerPage(0);
    $view->setCurrentPage(0);
    $view->execute();

    $test_data = $this->testNodesData;
    foreach ($view->result as $row) {
      $entity = $this->vboDataService->getEntityDefault($row, 'none', $view);

      $expected_label = $test_data[$entity->id()][$entity->language()->getId()];

      $this->assertEquals($expected_label, $entity->label(), 'Title matches');
      if ($expected_label === $entity->label()) {
        unset($test_data[$entity->id()][$entity->language()->getId()]);
        if (empty($test_data[$entity->id()])) {
          unset($test_data[$entity->id()]);
        }
      }
    }

    $this->assertEmpty($test_data, 'All created entities and their translations were returned.');
  }

}
