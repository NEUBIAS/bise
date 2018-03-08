<?php

namespace Drupal\Tests\feeds\Kernel\Feeds\Processor;

use Drupal\Core\Form\FormStateInterface;
use Drupal\feeds\Feeds\Processor\EntityProcessorBase;
use Drupal\feeds\FeedInterface;
use Drupal\feeds\FeedTypeInterface;
use Drupal\feeds\Feeds\Item\ItemInterface;
use Drupal\feeds\State;
use Drupal\Tests\feeds\Kernel\FeedsKernelTestBase;

/**
 * @coversDefaultClass \Drupal\feeds\Feeds\Processor\EntityProcessorBase
 * @group feeds
 */
class EntityProcessorBaseTest extends FeedsKernelTestBase {

  /**
   * The processor under test.
   *
   * @var \Drupal\feeds\Feeds\Fetcher\EntityProcessorBase
   */
  protected $processor;

  /**
   * The feed type entity.
   *
   * @var \Drupal\feeds\FeedTypeInterface
   */
  protected $feedType;

  /**
   * The feed entity.
   *
   * @var \Drupal\feeds\FeedInterface
   */
  protected $feed;

  /**
   * The state.
   *
   * @var \Drupal\feeds\State
   *
   * @todo replace with StateInterface.
   */
  protected $state;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->feedType = $this->getMock(FeedTypeInterface::class);
    $this->feedType->expects($this->any())
      ->method('getMappings')
      ->will($this->returnValue([]));

    $this->processor = $this->getMockForAbstractClass(EntityProcessorBase::class, [
      [
        'values' => [
          'type' => 'article',
        ],
        'feed_type' => $this->feedType,
      ],
      'entity:node',
      [
        'id' => 'entity:node',
        'title' => 'Node',
        'description' => 'Creates nodes from feed items.',
        'entity_type' => 'node',
        'arguments' => [
          '@entity_type.manager',
          '@entity.query',
          '@entity_type.bundle.info',
        ],
        'form' => [
          'configuration' => 'Drupal\feeds\Feeds\Processor\Form\DefaultEntityProcessorForm',
          'option' => 'Drupal\feeds\Feeds\Processor\Form\EntityProcessorOptionForm',
        ],
        'class' => EntityProcessorBase::class,
        'provider' => 'feeds',
        'plugin_type' => 'processor',
      ],
      \Drupal::service('entity_type.manager'),
      \Drupal::service('entity.query'),
      \Drupal::service('entity_type.bundle.info'),
    ]);

    $this->feed = $this->getMock(FeedInterface::class);
    $this->feed->expects($this->any())
      ->method('id')
      ->will($this->returnValue(1));

    $this->state = new State();

    // Install key/value expire schema.
    $this->installSchema('system', ['key_value_expire']);
  }

  /**
   * @covers ::process
   */
  public function testProcess() {
    $item = $this->getMock(ItemInterface::class);
    $this->processor->process($this->feed, $item, $this->state);

    // @todo This method should be tested with multiple times with different
    // settings.
    $this->markTestIncomplete('Test is a stub.');
  }

  /**
   * @covers ::clear
   */
  public function testClear() {
    $this->markTestIncomplete('Test not yet implemented.');
    $this->processor->clear($this->feed, $this->state);
  }

  /**
   * @covers ::entityType
   */
  public function testEntityType() {
    $this->assertEquals('node', $this->processor->entityType());
  }

  /**
   * @covers ::bundleKey
   */
  public function testBundleKey() {
    $this->assertEquals('type', $this->processor->bundleKey());
  }

  /**
   * @covers ::bundle
   */
  public function testBundle() {
    $this->assertEquals('article', $this->processor->bundle());
  }

  /**
   * @covers ::bundleLabel
   */
  public function testBundleLabel() {
    $this->assertEquals('Content type', $this->processor->bundleLabel());
  }

  /**
   * @covers ::bundleOptions
   */
  public function testBundleOptions() {
    $expected = [
      'article' => 'Article',
    ];
    $this->assertEquals($expected, $this->processor->bundleOptions());
  }

  /**
   * @covers ::entityTypeLabel
   */
  public function testEntityTypeLabel() {
    $this->assertEquals('Content', $this->processor->entityTypeLabel());
  }

  /**
   * @covers ::entityTypeLabelPlural
   */
  public function testEntityTypeLabelPlural() {
    $this->assertEquals('Contents', $this->processor->entityTypeLabelPlural());
  }

  /**
   * @covers ::getItemLabel
   */
  public function testGetItemLabel() {
    $this->assertEquals('Article', $this->processor->getItemLabel());
  }

  /**
   * @covers ::getItemLabelPlural
   */
  public function testGetItemLabelPlural() {
    $this->assertEquals('Articles', $this->processor->getItemLabelPlural());
  }

  /**
   * @covers ::defaultConfiguration
   */
  public function testDefaultConfiguration() {
    $this->assertInternalType('array', $this->processor->defaultConfiguration());
  }

  /**
   * @covers ::onFeedTypeSave
   */
  public function testOnFeedTypeSave() {
    $this->processor->onFeedTypeSave();
  }

  /**
   * @covers ::onFeedTypeDelete
   */
  public function testOnFeedTypeDelete() {
    $this->processor->onFeedTypeDelete();
  }

  /**
   * @covers ::expiryTime
   */
  public function testExpiryTime() {
    $this->assertEquals(EntityProcessorBase::EXPIRE_NEVER, $this->processor->expiryTime());

    // Change the expire setting.
    $config = $this->processor->getConfiguration();
    $config['expire'] = 100;
    $this->processor->setConfiguration($config);
    $this->assertEquals(100, $this->processor->expiryTime());
  }

  /**
   * @covers ::getExpiredIds
   */
  public function testGetExpiredIds() {
    $this->markTestIncomplete('Test not yet implemented.');
    $this->processor->getExpiredIds($this->feed);
  }

  /**
   * @covers ::expireItem
   */
  public function testExpireItem() {
    $this->markTestIncomplete('Test not yet implemented.');
    $item_id = 1;
    $this->processor->expireItem($this->feed, $item_id, $this->state);
  }

  /**
   * @covers ::getItemCount
   */
  public function testGetItemCount() {
    $this->markTestIncomplete('Test not yet implemented.');
    $this->processor->getItemCount($this->feed);
  }

  /**
   * @covers ::buildAdvancedForm
   */
  public function testBuildAdvancedForm() {
    $form = [];
    $form_state = $this->getMock(FormStateInterface::class);
    $this->assertInternalType('array', $this->processor->buildAdvancedForm($form, $form_state));
  }

  /**
   * @covers ::isLocked
   */
  public function testIsLocked() {
    $this->processor->isLocked();
    $this->markTestIncomplete('Test is a stub.');
  }

  /**
   * @covers ::onFeedDeleteMultiple
   */
  public function testOnFeedDeleteMultiple() {
    // Add feeds_item field to article content type.
    $this->callProtectedMethod($this->processor, 'prepareFeedsItemField');

    $this->processor->onFeedDeleteMultiple([$this->feed]);
    $this->markTestIncomplete('Test is a stub.');
  }

}
