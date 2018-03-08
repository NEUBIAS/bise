<?php

namespace Drupal\synonyms\SynonymsService\Behavior;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\synonyms\SynonymInterface;
use Drupal\synonyms\SynonymsProviderInterface\SynonymsFormatWordingProviderInterface;
use Drupal\synonyms\SynonymsProviderInterface\SynonymsGetProviderInterface;
use Drupal\synonyms\SynonymsService\BehaviorService;

/**
 * Synonyms behavior service for select widget.
 */
class SelectService implements SynonymsBehaviorConfigurableInterface {

  use StringTranslationTrait;

  /**
   * @var BehaviorService
   */
  protected $behaviorService;

  /**
   * @var RendererInterface
   */
  protected $renderer;

  /**
   * SelectService constructor.
   */
  public function __construct(BehaviorService $behavior_service, RendererInterface $renderer) {
    $this->behaviorService = $behavior_service;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, array $configuration, SynonymInterface $synonym_config) {
    $replacements = [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#items' => [],
    ];
    foreach ($synonym_config->getProviderPluginInstance()->formatWordingAvailableTokens() as $token => $token_info) {
      $replacements['#items'][] = Html::escape($token) . ': ' . $token_info;
    }

    $replacements = $this->renderer->renderRoot($replacements);

    $form['wording'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Wording for select entry'),
      '#default_value' => $configuration['wording'],
      '#description' => $this->t('Specify the wording with which the select entry should be presented. Available replacement tokens are: @replacements', [
        '@replacements' => $replacements,
      ]),
      '#required' => TRUE,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state, SynonymInterface $synonym_config) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state, SynonymInterface $synonym_config) {
    return [
      'wording' => $form_state->getValue('wording'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Select');
  }

  /**
   * {@inheritdoc}
   */
  public function getRequiredInterfaces() {
    return [
      SynonymsGetProviderInterface::class,
      SynonymsFormatWordingProviderInterface::class,
    ];
  }

  /**
   * Extract a list of synonyms from an entity.
   *
   * @param ContentEntityInterface $entity
   *   Entity from which to extract the synonyms
   *
   * @return array
   *   Array of synonyms. Each sub array will have the following structure:
   *   - synonym: (string) Synonym itself
   *   - wording: (string) Formatted wording with which this synonym should be
   *     presented to the end user
   */
  public function getSynonyms(ContentEntityInterface $entity) {
    $synonyms = $this->getSynonymsMultiple([$entity->id() => $entity]);
    return $synonyms[$entity->id()];
  }

  /**
   * Extract a list of synonyms from multiple entities.
   *
   * @param ContentEntityInterface[] $entities
   *   Array of entities from which to extract the synonyms. It should be keyed
   *   by entity ID and may only contain entities of the same type and bundle
   *
   * @return array
   *   Array of synonyms. The returned array will be keyed by entity ID and the
   *   inner array will have the following structure:
   *   - synonym: (string) Synonym itself
   *   - wording: (string) Formatted wording with which this synonym should be
   *     presented to the end user
   */
  public function getSynonymsMultiple(array $entities) {
    if (empty($entities)) {
      return [];
    }

    $synonym_configs = $this->behaviorService->getSynonymConfigEntities('synonyms.behavior.select', reset($entities)->getEntityTypeId(), reset($entities)->bundle());

    $synonyms = [];
    foreach ($entities as $entity) {
      $synonyms[$entity->id()] = [];
    }

    foreach ($synonym_configs as $synonym_config) {
      foreach ($synonym_config->getProviderPluginInstance()->getSynonymsMultiple($entities, $synonym_config->getBehaviorConfiguration()) as $entity_id => $entity_synonyms) {
        foreach ($entity_synonyms as $entity_synonym) {
          $synonyms[$entity_id][] = [
            'synonym' => $entity_synonym,
            'wording' => $synonym_config->getProviderPluginInstance()->synonymFormatWording($entity_synonym, $entities[$entity_id], $synonym_config),
          ];
        }
      }
    }

    return $synonyms;
  }

}
