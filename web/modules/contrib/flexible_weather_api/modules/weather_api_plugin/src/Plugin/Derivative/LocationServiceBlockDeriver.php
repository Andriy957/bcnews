<?php

namespace Drupal\weather_api_plugin\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\flexible_weather_api\WeatherApiLocationServicePluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LocationServiceBlockDeriver.
 *
 * @package Drupal\weather_api_plugin\Deriver
 */
class LocationServiceBlockDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The location service manager.
   *
   * @var \Drupal\flexible_weather_api\WeatherApiLocationServicePluginManager
   */
  protected $locationService;

  /**
   * LocationServiceBlockDeriver constructor.
   *
   * @param \Drupal\flexible_weather_api\WeatherApiLocationServicePluginManager $location_service
   *   The entity type bundle info.
   */
  public function __construct(WeatherApiLocationServicePluginManager $location_service) {
    $this->locationService = $location_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('plugin.manager.weather_api_location_service_plugin')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    foreach ($this->locationService->getDefinitions() as $plugin_id => $plugin) {
      $this->derivatives[$plugin_id] = $base_plugin_definition;
      $admin_label = new TranslatableMarkup('Weather with Location service "@plugin_label"', [
        '@plugin_label' => $plugin['label'],
      ]);
      $this->derivatives[$plugin_id]['admin_label'] = $admin_label;
    }

    return $this->derivatives;
  }

}
