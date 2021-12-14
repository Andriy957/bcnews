<?php

namespace Drupal\flexible_weather_api\Controller;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\flexible_weather_api\WeatherApiLocationServicePluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * LocationServicesController for the flexible_weather_api module.
 */
class LocationServicesController extends ControllerBase {

  /**
   * The weather api location service plugin manager.
   *
   * @var \Drupal\flexible_weather_api\WeatherApiLocationServicePluginManager
   */
  protected $weatherApiLocationServicePluginManager;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a LocationServicesController object.
   *
   * @param \Drupal\flexible_weather_api\WeatherApiLocationServicePluginManager $weather_api_location_service_plugin_manager
   *   The weather api location service plugin manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(WeatherApiLocationServicePluginManager $weather_api_location_service_plugin_manager, RendererInterface $renderer) {
    $this->weatherApiLocationServicePluginManager = $weather_api_location_service_plugin_manager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.weather_api_location_service_plugin'),
      $container->get('renderer')
    );
  }

  /**
   * Render the list of location services.
   *
   * @return array
   *   Return list of services.
   *
   * @throws \Exception
   */
  public function buildAvailableLocationServices() {
    $rows = [];
    foreach ($this->weatherApiLocationServicePluginManager->getDefinitions() as $plugin_id => $plugin) {
      $operations = [
        '#type' => 'operations',
        '#links' => [
          'edit' => [
            'title' => $this->t('Edit'),
            'url' => Url::fromRoute('flexible_weather_api.weather_api_location_service_plugin_configuration', ['location_service' => $plugin_id]),
          ],
        ],
      ];
      $operations = $this->renderer->render($operations);
      $rows[] = [$plugin['label'], new FormattableMarkup($operations, [])];
    }

    $header = [
      $this->t('Plugin Name'),
      $this->t('Operations'),
    ];

    return [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];
  }

  /**
   * Title callback.
   *
   * @param string $location_service
   *   The plugin id.
   *
   * @return string
   *   Return the title of the page.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getPageTitle(string $location_service) {
    $plugin_definition = $this->weatherApiLocationServicePluginManager->getDefinition($location_service);

    return isset($plugin_definition['overridden_label']) ? $plugin_definition['overridden_label'] : $plugin_definition['label'];
  }

}
