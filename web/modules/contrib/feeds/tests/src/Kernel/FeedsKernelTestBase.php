<?php

namespace Drupal\Tests\feeds\Kernel;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\feeds\Traits\FeedCreationTrait;
use Drupal\Tests\feeds\Traits\FeedsCommonTrait;
use Drupal\Tests\feeds\Traits\FeedsReflectionTrait;

/**
 * Provides a base class for Feeds kernel tests.
 */
abstract class FeedsKernelTestBase extends EntityKernelTestBase {

  use FeedCreationTrait;
  use FeedsCommonTrait;
  use FeedsReflectionTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['node', 'feeds'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Install database schemes.
    $this->installEntitySchema('feeds_feed');
    $this->installEntitySchema('feeds_subscription');
    $this->installSchema('node', 'node_access');

    // Create a content type.
    $type = NodeType::create([
      'type' => 'article',
      'name' => 'Article',
    ]);
    $type->save();
  }

}
