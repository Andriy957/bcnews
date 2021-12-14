<?php

namespace Drupal\flexible_weather_api;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Provides an WeatherApiLocationService plugin manager.
 *
 * @see plugin_api
 */
class WeatherApiLocationServicePluginManager extends DefaultPluginManager {

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $config;

  /**
   * Creates the discovery object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Config\ConfigFactory $config
   *   The config factory service.
   */
  public function __construct(\Traversable $namespaces,
                              CacheBackendInterface $cache_backend,
                              ModuleHandlerInterface $module_handler,
                              ConfigFactory $config) {
    parent::__construct(
      'Plugin/WeatherApiLocationServicePlugin',
      $namespaces,
      $module_handler,
      'Drupal\flexible_weather_api\WeatherApiLocationServicePluginInterface',
      'Drupal\flexible_weather_api\Annotation\WeatherApiLocationServicePlugin'
    );
    $this->config = $config;
    $this->alterInfo('weather_api_location_service_plugin_info');
    $this->setCacheBackend($cache_backend, 'weather_api_location_service_plugin');
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = []) {
    $configuration += (array) $this->config->get('flexible_weather_api.location_service')->get($plugin_id);
    return parent::createInstance($plugin_id, $configuration);
  }

}
