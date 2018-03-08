<?php

namespace Drupal\synonyms\SynonymsService\Behavior;

/**
 * Interface of a synonyms behavior. All behaviors must implement it.
 */
interface SynonymsBehaviorInterface {

  /**
   * Get human readable title of this behavior.
   *
   * @return string
   */
  public function getTitle();

  /**
   * Get a list of interfaces required from synonyms provider plugins.
   *
   * Get a list of PHP interfaces a synonyms provider plugin must implement in
   * order to support this behavior.
   *
   * @return array
   *   Array of PHP interfaces a synonyms provider plugin must implement in
   *   order to support this behavior
   */
  public function getRequiredInterfaces();

}
