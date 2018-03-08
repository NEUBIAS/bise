<?php

namespace Drupal\search_autocomplete\Plugin\views\display;

use Drupal\Component\Render\MarkupTrait;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheableResponse;
use Drupal\Core\ContentNegotiation;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\State\StateInterface;
use Drupal\views\Plugin\views\display\PathPluginBase;
use Drupal\views\Plugin\views\display\ResponseDisplayPluginInterface;
use Drupal\views\ViewExecutable;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The plugin that handles Data response callbacks for REST resources.
 *
 * @ingroup views_display_plugins
 *
 * @ViewsDisplay(
 *   id = "autocompletion_callback",
 *   title = @Translation("Autocompletion Callback"),
 *   help = @Translation("Create an autocompletion callback resource."),
 *   uses_route = TRUE,
 *   admin = @Translation("Autocompletion Callback"),
 *   returns_response = TRUE,
 *   autocompletion_callback_display = TRUE
 * )
 */
class AutocompletionCallback extends PathPluginBase implements ResponseDisplayPluginInterface {

  /**
   * Overrides \Drupal\views\Plugin\views\display\DisplayPluginBase::$usesAJAX.
   */
  protected $usesAJAX = FALSE;

  /**
   * Overrides \Drupal\views\Plugin\views\display\DisplayPluginBase::$usesPager.
   */
  protected $usesPager = FALSE;

  /**
   * Overrides \Drupal\views\Plugin\views\display\DisplayPluginBase::$usesMore.
   */
  protected $usesMore = FALSE;

  /**
   * Overrides \Drupal\views\Plugin\views\display\DisplayPluginBase::$usesAreas.
   */
  protected $usesAreas = FALSE;

  /**
   * Overrides
   * \Drupal\views\Plugin\views\display\DisplayPluginBase::$usesOptions.
   */
  protected $usesOptions = TRUE;

  /**
   * Overrides the content type of the data response, if needed.
   *
   * @var string
   */
  protected $contentType = 'application/json';

  /**
   * The mime type for the response.
   *
   * @var string
   */
  protected $mimeType;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   The route provider
   * @param \Drupal\Core\State\StateInterface $state
   *   The state key value store.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteProviderInterface $route_provider, StateInterface $state, RendererInterface $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $route_provider, $state);

    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
        $configuration,
        $plugin_id,
        $plugin_definition,
        $container->get('router.route_provider'),
        $container->get('state'),
        $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function buildResponse($view_id, $display_id, array $args = []) {
    $build = static::buildBasicRenderable($view_id, $display_id, $args);

    // @var \Drupal\Core\Render\RendererInterface $renderer.
    $renderer = \Drupal::service('renderer');

    $output = $renderer->renderRoot($build);

    $response = new CacheableResponse($output, 200);
    $cache_metadata = CacheableMetadata::createFromRenderArray($build);
    $response->addCacheableDependency($cache_metadata);

    $response->headers->set('Content-type', 'application/json');

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function initDisplay(ViewExecutable $view, array &$display, array &$options = NULL) {
    parent::initDisplay($view, $display, $options);

    $request_content_type = $this->view->getRequest()->getRequestFormat();
    $this->setContentType($request_content_type);
    $this->setMimeType($this->view->getRequest()->getMimeType($this->contentType));
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return 'callback';
  }

  /**
   * Sets the request content type.
   *
   * @param string $mime_type
   *   The response mime type. E.g. 'application/json'.
   */
  public function setMimeType($mime_type) {
    $this->mimeType = $mime_type;
  }

  /**
   * Gets the mime type.
   *
   * This will return any overridden mime type, otherwise returns the mime type
   * from the request.
   *
   * @return string
   *   The response mime type. E.g. 'application/json'.
   */
  public function getMimeType() {
    return $this->mimeType;
  }

  /**
   * Sets the content type.
   *
   * @param string $content_type
   *   The content type machine name. E.g. 'json'.
   */
  public function setContentType($content_type) {
    $this->contentType = $content_type;
  }

  /**
   * Gets the content type.
   *
   * @return string
   *   The content type machine name. E.g. 'json'.
   */
  public function getContentType() {
    return $this->contentType;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    // Set the default style plugin to 'json'.
    $options['style']['contains']['type']['default'] = 'callback_serializer';
    $options['row']['contains']['type']['default'] = 'callback_fields';
    $options['defaults']['default']['style'] = FALSE;
    $options['defaults']['default']['row'] = FALSE;

    // Remove css/exposed form settings,
    // as they are not used for the data display.
    unset($options['exposed_form']);
    unset($options['exposed_block']);
    unset($options['css_class']);

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function optionsSummary(&$categories, &$options) {
    parent::optionsSummary($categories, $options);

    unset($categories['page'], $categories['exposed']);
    // Hide some settings, as they aren't useful for pure data output.
    unset($options['show_admin_links'], $options['analyze-theme']);

    $categories['path'] = array(
      'title' => $this->t('Path settings'),
      'column' => 'second',
      'build' => array(
        '#weight' => -10,
      ),
    );

    $options['path']['category'] = 'path';
    $options['path']['title'] = $this->t('Path');

    // Remove css/exposed form settings, as they are not used for the data
    // display.
    unset($options['exposed_form']);
    unset($options['exposed_block']);
    unset($options['css_class']);
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    parent::execute();

    return $this->view->render();
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = array();

    $build['#markup'] = $this->renderer->executeInRenderContext(new RenderContext(), function() {
      return $this->view->style_plugin->render();
    });

    $this->view->element['#content_type'] = $this->getMimeType();
    $this->view->element['#cache_properties'][] = '#content_type';

    // Wrap the output in a pre tag if this is for a live preview.
    // Also little trick added to preview a formatted json for easier debug.
    if (!empty($this->view->live_preview)) {
      $dump = json_decode($build['#markup']);
      $build['#prefix'] = '<pre>';
      $build['#markup'] = json_encode($dump, JSON_PRETTY_PRINT);
      $build['#suffix'] = '</pre>';
    }
    elseif ($this->view->getRequest()->getFormat($this->view->element['#content_type']) !== 'html') {
      // This display plugin is primarily for returning non-HTML formats.
      // However, we still invoke the renderer to collect cacheability metadata.
      // Because the renderer is designed for HTML rendering, it filters
      // #markup for XSS unless it is already known to be safe, but that filter
      // only works for HTML. Therefore, we mark the contents as safe to bypass
      // the filter. So long as we are returning this in a non-HTML response
      // (checked above), this is safe, because an XSS attack only works when
      // executed by an HTML agent.
      // @todo Decide how to support non-HTML in the render API in
      //   https://www.drupal.org/node/2501313.
      $build['#markup'] = MarkupTrait::create($build['#markup']);
    }

    parent::applyDisplayCachablityMetadata($build);
    return $build;
  }

  /**
   * {@inheritdoc}
   *
   * The DisplayPluginBase preview method assumes we will be returning a render
   * array. The data plugin will already return the serialized string.
   */
  public function preview() {
    return $this->view->render();
  }
}
