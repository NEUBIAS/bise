<?php

namespace Drupal\Tests\feeds\Traits;

use Drupal\feeds\FeedInterface;
use Drupal\node\Entity\Node;

/**
 * Provides methods useful for Kernel and Functional Feeds tests.
 *
 * This trait is meant to be used only by test classes.
 */
trait FeedsCommonTrait {

  /**
   * Creates a new node with a feeds item field.
   *
   * @param \Drupal\feeds\FeedInterface $feed
   *   The feed with which the node was imported.
   * @param array $settings
   *   (optional) An associative array of settings for the node.
   *
   * @return \Drupal\node\NodeInterface
   *   The created node entity.
   */
  protected function createNodeWithFeedsItem(FeedInterface $feed, array $settings = []) {
    $settings += [
      'title'  => $this->randomMachineName(8),
      'type'  => 'article',
      'uid'  => 0,
      'feeds_item' => [
        'target_id' => $feed->id(),
        'imported' => 0,
        'guid' => 1,
        'hash' => static::randomString(),
      ],
    ];
    $node = Node::create($settings);
    $node->save();

    return $node;
  }

  /**
   * Asserts that the given number of nodes exist.
   *
   * @param int $expected_node_count
   *   The expected number of nodes in the node table.
   * @param string $message
   *   (optional) The message to assert.
   */
  protected function assertNodeCount($expected_node_count, $message = '') {
    if (!$message) {
      $message = '@expected nodes have been created (actual: @count).';
    }

    $node_count = $this->container->get('database')
      ->select('node')
      ->fields('node', [])
      ->countQuery()
      ->execute()
      ->fetchField();
    static::assertEquals($expected_node_count, $node_count, strtr($message, [
      '@expected' => $expected_node_count,
      '@count' => $node_count,
    ]));
  }

  /**
   * Absolute path to Drupal root.
   */
  protected function absolute() {
    return realpath(getcwd());
  }

  /**
   * Get the absolute directory path of the feeds module.
   */
  protected function absolutePath() {
    return $this->absolute() . '/' . drupal_get_path('module', 'feeds');
  }

  /**
   * Get the absolute directory path of the resources folder.
   */
  protected function resourcesPath() {
    return $this->absolutePath() . '/tests/resources';
  }

}
