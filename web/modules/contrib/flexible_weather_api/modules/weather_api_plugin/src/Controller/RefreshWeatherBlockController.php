<?php

namespace Drupal\weather_api_plugin\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\RendererInterface;
use Drupal\weather_api_plugin\WeatherApiPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * RefreshWeatherBlockController for the flexible_weather_api module.
 */
class RefreshWeatherBlockController extends ControllerBase {

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
   * Callback method for route weather_api_plugin.weather_api_ajax_refresh.
   *
   * Ajax update the block with the weather details.
   *
   * @param string $city
   *   The place for retrieving the weather data.
   * @param string $plugin_id
   *   The plugin id.
   * @param string $uuid
   *   The uuid for the weather data.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Return updated block.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function ajaxRefreshWeatherData($city, $plugin_id, $uuid) {
    $response = new AjaxResponse();
    $instance = $this->weatherApiPluginManager->createInstance($plugin_id);
    $build = $instance->build($city, $uuid, FALSE);
    $build = $this->renderer->render($build);
    $response->addCommand(new ReplaceCommand('.block-uuid-' . $uuid, $build));

    return $response;
  }

}
