<?php

namespace Drupal\synonyms\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides synonyms argument validator plugin definitions for all entity types.
 */
class ViewsSynonymsEntityArgumentValidator extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * The base plugin ID this derivative is for.
   *
   * @var string
   */
  protected $basePluginId;

  /**
   * The entity type manager.
   *
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs an ViewsSynonymsEntityArgumentValidator object.
   */
  public function __construct($base_plugin_id, EntityTypeManagerInterface $entity_type_manager, TranslationInterface $string_translation) {
    $this->basePluginId = $base_plugin_id;
    $this->entityTypeManager = $entity_type_manager;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $base_plugin_id,
      $container->get('entity_type.manager'),
      $container->get('string_translation')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $entity_types = $this->entityTypeManager->getDefinitions();
    $this->derivatives = array();
    foreach ($entity_types as $entity_type_id => $entity_type) {
      if ($entity_type instanceof ContentEntityTypeInterface) {
        $this->derivatives[$entity_type_id] = array(
          'id' => 'synonyms_entity:' . $entity_type_id,
          'provider' => 'synonyms',
          'title' => $this->t('Synonyms of @entity_type', [
            '@entity_type' => $entity_type->getLowercaseLabel(),
          ]),
          'help' => $this->t('Validate @label', array('@label' => $entity_type->getLabel())),
          'entity_type' => $entity_type_id,
          'class' => $base_plugin_definition['class'],
        );
      }
    }

    return $this->derivatives;
  }

}
