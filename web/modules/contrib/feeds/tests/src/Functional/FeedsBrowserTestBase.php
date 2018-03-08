<?php

namespace Drupal\Tests\feeds\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Provides a base class for Feeds functional tests.
 */
abstract class FeedsBrowserTestBase extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['feeds'];

}
