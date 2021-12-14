<?php

namespace Drupal\flexible_weather_api\Service;

/**
 * Interface WeatherApiRequesterInterface.
 *
 * @package Drupal\flexible_weather_api\Service
 */
interface WeatherApiRequesterInterface {

  /**
   * Define request method constants.
   */
  public const GET = 'GET';
  public const POST = 'POST';
  public const PUT = 'PUT';

  /**
   * Retrieving data.
   *
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $service_options
   *   The options for http request.
   *   Can contains:
   *   - api_key,
   *   - api_key_required,
   *   - query_params,
   *   - base_url,
   *   - language.
   * @param bool $getFromCache
   *   The bool value to getting data from cache.
   * @param string $method
   *   The request method.
   *
   * @return array|null
   *   Return retrieved data.
   */
  public function retrieveData(string $plugin_id, array $service_options, $getFromCache = TRUE, $method = self::GET);

}
