<?php

namespace Drupal\synonyms\SynonymsProviderInterface;

use Drupal\Core\Database\Query\ConditionInterface;

/**
 * Supportive trait to find synonyms.
 */
trait SynonymsFindTrait {

  /**
   * Supportive method to process $condition argument in synonymsFind().
   *
   * This method will swap SynonymsFindProviderInterface::COLUMN_* to real
   * column names in $condition for you, so you do not have to worry about
   * internal processing of $condition object.
   *
   * @param \Drupal\Core\Database\Query\ConditionInterface $condition
   *   Condition to be processed
   * @param string $synonym_column
   *   Actual name of the column where synonyms are kept in text
   * @param string $entity_id_column
   *   Actual name of the column where entity_ids are kept
   */
  public function synonymsFindProcessCondition(ConditionInterface $condition, $synonym_column, $entity_id_column) {
    $condition_array = &$condition->conditions();
    foreach ($condition_array as &$v) {
      if (is_array($v) && isset($v['field'])) {
        if ($v['field'] instanceof ConditionInterface) {
          // Recursively process this condition too.
          $this->synonymsFindProcessCondition($v['field'], $synonym_column, $entity_id_column);
        }
        else {
          $replace = array(
            SynonymsFindProviderInterface::COLUMN_SYNONYM_PLACEHOLDER => $synonym_column,
            SynonymsFindProviderInterface::COLUMN_ENTITY_ID_PLACEHOLDER => $entity_id_column,
          );
          $v['field'] = str_replace(array_keys($replace), array_values($replace), $v['field']);
        }
      }
    }
  }

}
