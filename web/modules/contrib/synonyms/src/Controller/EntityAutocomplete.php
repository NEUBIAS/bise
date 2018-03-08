<?php

namespace Drupal\synonyms\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Tags;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\synonyms\SynonymsService\Behavior\AutocompleteService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Synonyms friendly entity autocomplete controller.
 */
class EntityAutocomplete extends ControllerBase {

  /**
   * @var AutocompleteService
   */
  protected $autocompleteService;

  /**
   * EntityAutocomplete constructor.
   */
  public function __construct(AutocompleteService $autocomplete_service) {
    $this->autocompleteService = $autocomplete_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('synonyms.behavior.autocomplete')
    );
  }

  /**
   * Routing callback: provide suggestions for autocomplete form element.
   */
  public function autocomplete(Request $request, EntityTypeInterface $target_type, $token) {
    $matches = [];

    if ($input = $request->get('q')) {
      $input = Tags::explode($input);
      $input = array_pop($input);
      foreach ($this->autocompleteService->autocompleteLookup($input, $token) as $k => $suggestion) {
        $key = $suggestion['entity_label'] . ' (' . $suggestion['entity_id'] . ')';
        $key = preg_replace('/\s\s+/', ' ', str_replace("\n", '', trim(Html::decodeEntities(strip_tags($key)))));

        // Names containing commas or quotes must be wrapped in quotes.
        $key = Tags::encode($key);

        $matches[] = [
          'value' => $key,
          'label' => $suggestion['wording'],
        ];
      }
    }

    return new JsonResponse($matches);
  }

}
