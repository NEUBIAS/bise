<?php

namespace Drupal\synonyms\ParamConverter;

use Drupal\Core\ParamConverter\ParamConverterInterface;
use Drupal\synonyms\SynonymsService\BehaviorService;
use Symfony\Component\Routing\Route;

/**
 * Param converter for synonyms behavior service.
 */
class SynonymsBehaviorServiceParamConverter implements ParamConverterInterface {

  /**
   * @var BehaviorService
   */
  protected $behaviorService;

  /**
   * SynonymsBehaviorServiceParamConverter constructor.
   */
  public function __construct(BehaviorService $behavior_service) {
    $this->behaviorService = $behavior_service;
  }

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults) {
    return $this->behaviorService->getBehaviorService($value)['service'];
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route) {
    return (!empty($definition['type']) && $definition['type'] == 'synonyms_behavior_service');
  }

}
