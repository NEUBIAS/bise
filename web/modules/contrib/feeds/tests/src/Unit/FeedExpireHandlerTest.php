<?php

namespace Drupal\Tests\feeds\Unit;

use Drupal\feeds\Event\FeedsEvents;
use Drupal\feeds\FeedExpireHandler;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @coversDefaultClass \Drupal\feeds\FeedExpireHandler
 * @group feeds
 */
class FeedExpireHandlerTest extends FeedsUnitTestCase {

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
    $this->feed = $this->getMock('Drupal\feeds\FeedInterface');
  }

  /**
   * @covers ::startBatchExpire
   */
  public function testBatchExpire() {
    $this->markTestIncomplete('The expire functionality is not working yet.');
  }

  /**
   * @covers ::expireItem
   */
  public function testExpireItem() {
    $this->markTestIncomplete('The expire functionality is not working yet.');

    $this->feed
      ->expects($this->exactly(2))
      ->method('progressExpiring')
      ->will($this->onConsecutiveCalls(0.5, 1.0));
    $this->feed
      ->expects($this->once())
      ->method('clearStates');

    $handler = new FeedExpireHandler($this->dispatcher);
    $result = $handler->expireItem($this->feed);
    $this->assertSame($result, 0.5);
    $result = $handler->expireItem($this->feed);
    $this->assertSame($result, 1.0);
  }

  /**
   * @covers ::postExpire
   */
  public function testPostExpire() {
    $this->markTestIncomplete('The expire functionality is not working yet.');
  }

  /**
   * @covers ::expireItem
   * @expectedException \Exception
   */
  public function testException() {
    $this->markTestIncomplete('The expire functionality is not working yet.');

    $this->dispatcher->addListener(FeedsEvents::EXPIRE, function ($event) {
      throw new \Exception();
    });

    $this->feed
      ->expects($this->once())
      ->method('clearStates');

    $handler = new FeedExpireHandler($this->dispatcher);
    $handler->expireItem($this->feed);
  }

}
