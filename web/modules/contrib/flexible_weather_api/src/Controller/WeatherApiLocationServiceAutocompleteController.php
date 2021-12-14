<?php

namespace Drupal\flexible_weather_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\flexible_weather_api\WeatherApiLocationServicePluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * WeatherApiLocationServiceController for the flexible_weather_api module.
 */
class WeatherApiLocationServiceAutocompleteController extends ControllerBase {

  /**
   * The weather api location service plugin manager.
   *
   * @var \Drupal\flexible_weather_api\WeatherApiLocationServicePluginManager
   */
  protected $weatherApiLocationServicePluginManager;

  /**
   * Constructs a LocationServicesController object.
   *
   * @param \Drupal\flexible_weather_api\WeatherApiLocationServicePluginManager $weather_api_location_service_plugin_manager
   *   The weather api location service plugin manager.
   */
  public function __construct(WeatherApiLocationServicePluginManager $weather_api_location_service_plugin_manager) {
    $this->weatherApiLocationServicePluginManager = $weather_api_location_service_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.weather_api_location_service_plugin')
    );
  }

  /**
   * Callback Method for Route flexible_weather_api.location_service.weather_api_location_service_geonames.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The Request sent.
   *
   * @return mixed|string
   *   Json output of the found strings.
   */
  public function geonamesLocationAutocomplete(Request $request) {
    // @todo Find a way for pass plugin id dynamically to the controller.
    $plugin_id = 'weather_api_location_service_geonames';
    $matches = $this->getAddressMatches($request, $plugin_id);

    return new JsonResponse($matches);
  }

  /**
   * Callback Method for Route flexible_weather_api.location_service.weather_api_location_service_geonames.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The Request sent.
   *
   * @return mixed|string
   *   Json output of the found strings.
   */
  public function googleLocationAutocomplete(Request $request) {
    $plugin_id = 'weather_api_location_service_google';
    $matches = $this->getAddressMatches($request, $plugin_id);

    return new JsonResponse($matches);
  }

  /**
   * Helper function for get matches from location service plugin.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The Request sent.
   * @param string $plugin_id
   *   The id of plugin.
   *
   * @return mixed
   *   Array of matches.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  private function getAddressMatches(Request $request, $plugin_id) {
    $instance = $this->weatherApiLocationServicePluginManager->createInstance($plugin_id);

    return $request->query->get('q') ? $instance->getAddress($request->query->get('q')) : [];
  }

}
