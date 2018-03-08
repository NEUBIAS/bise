<?php

namespace Drupal\synonyms\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Url;
use Drupal\synonyms\SynonymsService\Behavior\SynonymsBehaviorInterface;
use Drupal\synonyms\SynonymsService\BehaviorService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for admin UI of the module.
 */
class SynonymConfigController extends ControllerBase {

  /**
   * @var EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * @var BehaviorService
   */
  protected $behaviorService;

  /**
   * SynonymConfigController constructor.
   */
  public function __construct(EntityTypeBundleInfoInterface $entity_type_bundle_info, BehaviorService $behavior_service) {
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
    $this->behaviorService = $behavior_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.bundle.info'),
      $container->get('synonyms.behaviors')
    );
  }

  /**
   * Routing callback: show overview table of synonyms configuration.
   */
  public function overview() {
    $render = array(
      '#type' => 'table',
      '#header' => [
        $this->t('Entity type'),
        $this->t('Bundle'),
        $this->t('Manage'),
      ],
    );

    foreach ($this->entityTypeManager()->getDefinitions() as $entity_type) {
      if ($entity_type instanceof ContentEntityTypeInterface) {
        foreach ($this->entityTypeBundleInfo->getBundleInfo($entity_type->id()) as $bundle => $bundle_info) {
          $links = [];
          foreach ($this->behaviorService->getBehaviorServices() as $service_id => $service) {
            $count = count($this->behaviorService->getSynonymConfigEntities($service_id, $entity_type->id(), $bundle));

            if ($count > 0) {
              $title = $this->t('@behavior (@count)', [
                '@behavior' => $service['service']->getTitle(),
                '@count' => $count,
              ]);
            }
            else {
              $title = $service['service']->getTitle();
            }

            $links[$service_id] = [
              'title' => $title,
              'url' => Url::fromRoute('entity.synonym.overview.entity_type.bundle.behavior', [
                'synonyms_entity_type' => $entity_type->id(),
                'bundle' => $bundle,
                'synonyms_behavior_service' => $service_id,
              ]),
            ];
          }

          $render[] = [
            ['#markup' => Html::escape($entity_type->getLabel())],
            ['#markup' => $bundle == $entity_type->id() ? '' : Html::escape($bundle_info['label'])],
            ['#type' => 'operations', '#links' => $links],
          ];
        }
      }
    }

    return $render;
  }

  /**
   * Routing callback to overview a particular entity type and behavior service.
   */
  public function overviewEntityTypeBundle(EntityTypeInterface $synonyms_entity_type, $bundle, SynonymsBehaviorInterface $synonyms_behavior_service) {
    $table = [
      '#type' => 'table',
      '#header' => [
        $this->t('Provider'),
        $this->t('Operations'),
      ],
    ];

    foreach ($this->entityTypeManager()->getStorage('synonym')->loadMultiple() as $synonym_config) {
      $provider_instance = $synonym_config->getProviderPluginInstance();
      $provider_definition = $provider_instance->getPluginDefinition();
      if ($provider_definition['controlled_entity_type'] == $synonyms_entity_type->id() && $provider_definition['controlled_bundle'] == $bundle && $this->behaviorService->getBehaviorService($provider_definition['synonyms_behavior_service'])['service'] == $synonyms_behavior_service) {
        $table[] = [
          ['#markup' => Html::escape($synonym_config->label())],
          [
            '#type' => 'operations',
            '#links' => $this->entityTypeManager()->getListBuilder($synonym_config->getEntityTypeId())->getOperations($synonym_config),
          ],
        ];
      }
    }

    return $table;
  }

  /**
   * Title callback for 'entity.synonym.overview.entity_type.bundle.behavior'.
   */
  public function overviewEntityTypeBundleTitle(EntityTypeInterface $synonyms_entity_type, $bundle, SynonymsBehaviorInterface $synonyms_behavior_service) {
    if ($synonyms_entity_type->id() == $bundle) {
      return $this->t('Manage @behavior of @entity_type', [
        '@behavior' => $synonyms_behavior_service->getTitle(),
        '@entity_type' => $synonyms_entity_type->getLabel(),
      ]);
    }

    $bundle_info = $this->entityTypeBundleInfo->getBundleInfo($synonyms_entity_type->id());

    return $this->t('Manage @behavior of @entity_type @bundle', [
      '@behavior' => $synonyms_behavior_service->getTitle(),
      '@entity_type' => $synonyms_entity_type->getLabel(),
      '@bundle' => $bundle_info[$bundle]['label'],
    ]);
  }

}
