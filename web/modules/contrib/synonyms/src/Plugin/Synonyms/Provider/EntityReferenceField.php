<?php

namespace Drupal\synonyms\Plugin\Synonyms\Provider;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\ConditionInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryBase;
use Drupal\Core\Entity\Query\Sql\Condition;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\synonyms\SynonymsProviderInterface\SynonymsFindProviderInterface;
use Drupal\synonyms\SynonymsProviderInterface\SynonymsFindTrait;
use Drupal\synonyms\SynonymsProviderInterface\SynonymsFormatWordingProviderInterface;
use Drupal\synonyms\SynonymsProviderInterface\SynonymsFormatWordingTrait;
use Drupal\synonyms\SynonymsProviderInterface\SynonymsGetProviderInterface;
use Drupal\synonyms\SynonymsProviderInterface\SynonymsGetTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provide synonyms from entity reference field type.
 *
 * @SynonymsProvider(
 *   id = "entityreference_field",
 *   deriver = "Drupal\synonyms\Plugin\Derivative\EntityReferenceField"
 * )
 */
class EntityReferenceField extends AbstractProvider implements SynonymsGetProviderInterface, SynonymsFindProviderInterface, SynonymsFormatWordingProviderInterface {

  use SynonymsGetTrait, SynonymsFindTrait, SynonymsFormatWordingTrait;

  /**
   * @var EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var Connection
   */
  protected $database;

  /**
   * @var QueryFactory
   */
  protected $entityQueryFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityFieldManagerInterface $entity_field_manager, EntityTypeManagerInterface $entity_type_manager, Connection $database, QueryFactory $entity_query_factory, ContainerInterface $container) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $container);

    $this->entityFieldManager = $entity_field_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->database = $database;
    $this->entityQueryFactory = $entity_query_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_field.manager'),
      $container->get('entity_type.manager'),
      $container->get('database'),
      $container->get('entity.query'),
      $container
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getSynonyms(ContentEntityInterface $entity, array $behavior_configuration = []) {
    $synonyms = [];

    foreach ($entity->get($this->getPluginDefinition()['field']) as $item) {
      $synonyms[] = $item->entity->label();
    }

    return $synonyms;
  }

  /**
   * {@inheritdoc}
   */
  public function synonymsFind(ConditionInterface $condition) {
    $entity_type_definition = $this->entityTypeManager->getDefinition($this->getPluginDefinition()['controlled_entity_type']);
    $field = $this->entityFieldManager->getFieldDefinitions($this->getPluginDefinition()['controlled_entity_type'], $this->getPluginDefinition()['controlled_bundle']);
    $field = $field[$this->getPluginDefinition()['field']];

    $query = new FieldQuery($entity_type_definition, 'AND', $this->database, QueryBase::getNamespaces($this->entityQueryFactory->get($entity_type_definition->id(), 'AND')));

    if ($entity_type_definition->hasKey('bundle')) {
      $query->condition($entity_type_definition->getKey('bundle'), $this->getPluginDefinition()['controlled_bundle']);
    }

    $target_entity_type_definition = $this->entityTypeManager->getDefinition($field->getSetting('target_type'));
    $label_column = $target_entity_type_definition->getKey('label');

    // Some hacks.
    if (!$label_column && $target_entity_type_definition->id() == 'user') {
      $label_column = 'name';
    }

    if (!$label_column) {
      // No label column is defined on target entity type, there's nothing we
      // can do.
      return [];
    }

    $synonym_column = $field->getName() . '.entity.' . $label_column;
    $this->synonymsFindProcessCondition($condition, $synonym_column, $entity_type_definition->getKey('id'));

    $conditions_array = $condition->conditions();
    $entity_condition = new Condition($conditions_array['#conjunction'], $query);

    // We will insert a dummy condition for a synonym column just to have the
    // actual table.column data on the synonym column.
    $hash = md5($synonym_column);
    $query->condition($synonym_column, $hash);

    unset($conditions_array['#conjunction']);
    foreach ($conditions_array as $v) {
      $entity_condition->condition($v['field'], $v['value'], $v['operator']);
    }

    $query->condition($entity_condition);

    // We need run the entity query in order to force it into building a normal
    // SQL query.
    $query->execute();

    // Now let's get "demapped" normal SQL query that will tell us explicitly
    // where all entity properties/fields are stored among the SQL tables. Such
    // data is what we need and we do not want to demap it manually.
    $sql_query = $query->getSqlQuery();

    // Swap the entity_id column into the alias "entity_id" as we are supposed
    // to do in this method implementation.
    $select_fields = &$sql_query->getFields();
    $select_fields = ['entity_id' => $select_fields[$entity_type_definition->getKey('id')]];
    $select_fields['entity_id']['alias'] = 'entity_id';

    // We need some dummy extra condition to force them into re-compilation.
    $sql_query->where('1 = 1');
    $conditions_array = &$sql_query->conditions();
    foreach ($conditions_array as $k => $condition_array) {
      if (isset($condition_array['value']) && $condition_array['value'] == $hash) {
        list($table_alias, $column) = explode('.', $condition_array['field']);
        unset($conditions_array[$k]);

        // Also unsetting the table aliased by this condition.
        $tables = &$sql_query->getTables();
        $real_table = $tables[$table_alias]['table'];
        foreach ($tables as $k2 => $v2) {
          if ($k2 != $table_alias && $real_table == $v2['table']) {
            unset($tables[$table_alias]);
            $table_alias = $k2;
          }
        }

        $sql_query->addField($table_alias, $column, 'synonym');
        break;
      }
    }
    $result = $sql_query->execute();
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $field = $this->entityFieldManager->getFieldDefinitions($this->getPluginDefinition()['controlled_entity_type'], $this->getPluginDefinition()['controlled_bundle'])[$this->getPluginDefinition()['field']];
    return [
      $field->getConfigDependencyKey() => [$field->getConfigDependencyName()],
    ];
  }

}
