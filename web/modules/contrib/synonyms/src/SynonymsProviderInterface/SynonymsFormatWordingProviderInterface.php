<?php

namespace Drupal\synonyms\SynonymsProviderInterface;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\synonyms\SynonymInterface;

/**
 * Interface to format a synonym into some kind of wording.
 */
interface SynonymsFormatWordingProviderInterface {

  /**
   * Format a synonym into wording as requested by configuration.
   *
   * @param string $synonym
   *   Synonym that should be formatted
   * @param ContentEntityInterface $entity
   *   Entity to which this synonym belongs
   * @param SynonymInterface $synonym_config
   *   Synonym config entity in the context of which it all happens
   *
   * @return string
   *   Formatted wording
   */
  public function synonymFormatWording($synonym, ContentEntityInterface $entity, SynonymInterface $synonym_config);

  /**
   * @return array
   *   Array of supported tokens in wording. Keys are the tokens whereas
   *   corresponding values are explanations about what each token means
   */
  public function formatWordingAvailableTokens();

}
