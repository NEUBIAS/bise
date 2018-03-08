<?php

namespace Drupal\Tests\feeds\Unit;

use Drupal\feeds\FeedImportHandler;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @coversDefaultClass \Drupal\feeds\FeedImportHandler
 * @group feeds
 */
class FeedImportHandlerTest extends FeedsUnitTestCase {

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcher
   */
  protected $dispatcher;

  /**
   * The feed entity.
   *
   * @var \Drupal\feeds\FeedInterface
   */
  protected $feed;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->dispatcher = new EventDispatcher();
    $this->handler = new FeedImportHandler($this->dispatcher);
    $this->handler->setStringTranslation($this->getStringTranslationStub());

    $this->feed = $this->getMock('Drupal\feeds\FeedInterface');
    $this->feed->expects($this->any())
      ->method('id')
      ->will($this->returnValue(10));
    $this->feed->expects($this->any())
      ->method('bundle')
      ->will($this->returnValue('test_feed'));
  }

  /**
   * @covers ::startBatchImport
   */
  public function testStartBatchImport() {
    $this->feed->expects($this->once())
      ->method('lock')
      ->will($this->returnValue($this->feed));

    $this->handler->startBatchImport($this->feed);
  }

  /**
   * @covers ::batchFetch
   */
  public function testBatchFetch() {
    $this->handler->batchFetch($this->feed);
  }

}
