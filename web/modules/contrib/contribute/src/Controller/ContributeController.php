<?php

namespace Drupal\contribute\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ContributeController.
 */
class ContributeController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The Guzzle HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Constructs a ContributeController object.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The Guzzle HTTP client.
   */
  public function __construct(ClientInterface $http_client) {
    $this->httpClient = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client')
    );
  }

  /**
   * Returns account type autocomplete matches.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param string $account_type
   *   The account type to autocomplete.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   */
  public function autocomplete(Request $request, $account_type = 'user') {
    $q = $request->query->get('q');

    switch ($account_type) {
      case 'user':
        $response = $this->httpClient->get('https://www.drupal.org/index.php?q=admin/views/ajax/autocomplete/user/' . urlencode($q));
        $data = Json::decode($response->getBody());
        $matches = [];
        foreach ($data as $value) {
          $matches[] = ['value' => $value, 'label' => $value];
        }
        return new JsonResponse($matches);

      case 'organization':
        $response = $this->httpClient->get('https://www.drupal.org/index.php?q=entityreference/autocomplete/tags/field_for_customer/comment/comment_node_project_issue/NULL/' . urlencode($q));
        $data = Json::decode($response->getBody());
        $matches = [];
        foreach ($data as $value) {
          $value = strip_tags($value);
          $matches[] = ['value' => $value, 'label' => $value];
        }
        return new JsonResponse($matches);
    }
  }

}
