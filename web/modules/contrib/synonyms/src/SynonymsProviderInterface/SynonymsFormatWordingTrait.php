<?php

namespace Drupal\synonyms\SynonymsProviderInterface;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\synonyms\SynonymInterface;

/**
 * Trait to format wording of a synonym.
 */
trait SynonymsFormatWordingTrait {

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
  public function synonymFormatWording($synonym, ContentEntityInterface $entity, SynonymInterface $synonym_config) {
    // TODO: maybe we should use tokens replacement here? But then it would mean
    // an extra dependency on the tokens module. Is it worth it? For now let's
    // use stupid str_replace() and incorporate tokens only if user base really
    // asks for it.
    $map = [
      '@synonym' => $synonym,
      '@entity_label' => $entity->label(),
    ];
    return str_replace(array_keys($map), array_values($map), $synonym_config->getBehaviorConfiguration()['wording']);
  }

  /**
   * @return array
   *   Array of supported tokens in wording. Keys are the tokens whereas
   *   corresponding values are explanations about what each token means
   */
  public function formatWordingAvailableTokens() {
    return [
      '@synonym' => $this->t('actual synonym value'),
      '@entity_label' => $this->t('actual label of the entity this synonym belongs to'),
    ];
  }

}

