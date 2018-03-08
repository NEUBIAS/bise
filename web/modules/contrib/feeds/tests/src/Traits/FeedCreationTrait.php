<?php

namespace Drupal\Tests\feeds\Traits;

use Drupal\Component\Utility\Unicode;
use Drupal\feeds\Entity\Feed;
use Drupal\feeds\Entity\FeedType;
use Drupal\feeds\FeedInterface;
use Drupal\feeds\FeedTypeInterface;

/**
 * Provides methods to create feeds and feed types with default settings.
 *
 * This trait is meant to be used only by test classes.
 */
trait FeedCreationTrait {

  /**
   * Creates a feed type with default settings.
   *
   * @param array $settings
   *   (optional) An associative array of settings for the feed type entity.
   *   The following defaults are provided:
   *   - label: Random string.
   *   - ID: Random string.
   *   - import_period: never.
   *   - processor_configuration: authorize off and article bundle.
   *   - mappings: mapping to guid and title.
   *
   * @return \Drupal\feeds\FeedTypeInterface
   *   The created feed type entity.
   */
  protected function createFeedType(array $settings = []) {
    // Populate default array.
    $settings += [
      'id' => Unicode::strtolower($this->randomMachineName()),
      'label' => $this->randomMachineName(),
      'import_period' => FeedTypeInterface::SCHEDULE_NEVER,
      'processor_configuration' => [
        'authorize' => FALSE,
        'values' => [
          'type' => 'article',
        ],
      ],
      'mappings' => [
        [
          'target' => 'feeds_item',
          'map' => ['guid' => 'guid', 'url' => 'url'],
          'unique' => ['guid' => TRUE],
        ],
        [
          'target' => 'title',
          'map' => ['value' => 'title'],
        ],
      ],
    ];

    $feed_type = FeedType::create($settings);
    $feed_type->save();

    return $feed_type;
  }

  /**
   * Creates a feed with default settings.
   *
   * @param string $feed_type_id
   *   The feed type ID.
   * @param array $settings
   *   (optional) An associative array of settings for the feed entity.
   *   The following defaults are provided:
   *   - label: Random string.
   *
   * @return \Drupal\feeds\FeedTypeInterface
   *   The created feed type entity.
   */
  protected function createFeed($feed_type_id, array $settings = []) {
    // Populate default array.
    $settings += [
      'label' => $this->randomMachineName(),
    ];
    $settings['type'] = $feed_type_id;

    $feed = Feed::create($settings);
    $feed->save();

    return $feed;
  }

  /**
   * Reloads a feed entity.
   *
   * @param \Drupal\feeds\FeedInterface $feed
   *   The feed entity to reload.
   *
   * @return \Drupal\feeds\FeedInterface
   *   The reloaded feed.
   */
  protected function reloadFeed(FeedInterface $feed) {
    /** @var \Drupal\feeds\FeedStorageInterface $storage */
    $storage = \Drupal::entityTypeManager()->getStorage('feeds_feed');
    return $storage->load($feed->id());
  }

}
