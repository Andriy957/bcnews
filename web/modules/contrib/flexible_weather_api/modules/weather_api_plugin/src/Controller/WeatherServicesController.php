<?php

namespace Drupal\weather_api_plugin\Controller;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\weather_api_plugin\WeatherApiPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * WeatherServicesController for the flexible_weather_api module.
 */
class WeatherServicesController extends ControllerBase {

  /**
   * The weather api plugin manager.
   *
   * @var \Drupal\weather_api_plugin\WeatherApiPluginManager
   */
  protected $weatherApiPluginManager;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a WeatherServicesController object.
   *
   * @param \Drupal\weather_api_plugin\WeatherApiPluginManager $weather_api_plugin_manager
   *   The weather api plugin manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(WeatherApiPluginManager $weather_api_plugin_manager, RendererInterface $renderer) {
    $this->weatherApiPluginManager = $weather_api_plugin_manager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.weather_api_plugin'),
      $container->get('renderer')
    );
  }

  /**
   * Callback method for route weather_api_plugin.weather_service_configuration.
   *
   * Render the list of weather services.
   *
   * @return array
   *   Return list of services.
   */
  public function buildAvailableWeatherServices() {
    $rows = [];
    foreach ($this->weatherApiPluginManager->getDefinitions() as $plugin_id => $plugin) {
      $operations = [
        '#type' => 'operations',
        '#links' => [
          'edit' => [
            'title' => $this->t('Edit'),
            'url' => Url::fromRoute('weather_api_plugin.weather_service_configuration', ['weather_service' => $plugin_id]),
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
   * Title callback for route weather_api_plugin.weather_service_configuration.
   *
   * @param string $weather_service
   *   The plugin id.
   *
   * @return string
   *   Return the title of the page.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getPageTitle(string $weather_service) {
    $plugin_definition = $this->weatherApiPluginManager->getDefinition($weather_service);

    return isset($plugin_definition['overridden_label']) ? $plugin_definition['overridden_label'] : $plugin_definition['label'];
  }

}
