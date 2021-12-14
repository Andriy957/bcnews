<?php

namespace Drupal\flexible_weather_api\Service;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class WeatherApiRequester.
 *
 * @package Drupal\flexible_weather_api\Service
 */
class WeatherApiRequester implements WeatherApiRequesterInterface {

  use StringTranslationTrait;

  /**
   * ConfigFactory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Get Cache service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   *   Cache service.
   */
  private $cacheBackend;

  /**
   * Get Client http service.
   *
   * @var \GuzzleHttp\ClientInterface
   *   The HTTP client.
   */
  private $httpClient;

  /**
   * The time.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  private $time;

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  private $dateFormatter;

  /**
   * WeatherStation constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              CacheBackendInterface $cache_backend,
                              ClientInterface $http_client,
                              TimeInterface $time,
                              DateFormatterInterface $date_formatter) {
    $this->configFactory = $config_factory;
    $this->cacheBackend = $cache_backend;
    $this->httpClient = $http_client;
    $this->time = $time;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public function retrieveData(string $plugin_id, array $service_options, $getFromCache = TRUE, $method = self::GET) {
    $data = NULL;
    $cid = 'weather_service:' . $method . ':' . $plugin_id;
    if (isset($service_options['uuid'])) {
      $cid = $cid . ':' . $service_options['uuid'];
    }
    $cache = $this->cacheBackend->get($cid);
    if ($cache && $getFromCache) {
      $data = $cache->data;
    }
    else {
      $data = $this->requestData($plugin_id, $service_options, $method);
      if (!empty($data)) {
        if (is_object($data) && !isset($data->error)) {
          $this->cacheBackend->set($cid, $data, $this->time->getRequestTime() + 21600);
        }
        elseif (is_array($data) && !isset($data['error'])) {
          $this->cacheBackend->set($cid, $data, $this->time->getRequestTime() + 21600);
        }
      }
    }

    return $data;
  }

  /**
   * Retrieve data from endpoint.
   *
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $service_options
   *   The options for http request.
   *   Can contains:
   *   - api_key,
   *   - api_key_required,
   *   - query_params,
   *   - base_url.
   * @param string $method
   *   The request method.
   *
   * @return array
   *   Return response from the service.
   */
  private function requestData(string $plugin_id, array $service_options, $method = self::GET) {
    $service_data = [];
    $api_key = isset($service_options['api_key']) ? $service_options['api_key'] : '';
    if (empty($api_key) && isset($service_options['api_key_required']) && $service_options['api_key_required'] == TRUE) {
      return [
        'error' => $this->t('The API key is missing for @service', ['@service' => $plugin_id]),
      ];
    }
    $query = isset($service_options['query_params']) ? $service_options['query_params'] : [];
    if (isset($service_options['base_url']) && !empty($service_options['base_url'])) {
      $uri = $service_options['base_url'];
      $options = [
        'query' => $query,
      ];
      if (isset($service_options['headers'])) {
        $options['headers'] = $service_options['headers'];
      }
      try {
        $response = $this->httpClient->request($method, $uri, $options);
      }
      catch (GuzzleException $e) {
        // @todo Create log.
      }
      if (isset($response) && empty($response->error)) {
        $data = json_decode($response->getBody());
        if ($data) {
          $updated_time = '';
          $date_original = new DrupalDateTime('now');
          if (!$date_original->hasErrors()) {
            $site_timezone = $this->configFactory->get('system.date')->get('timezone')['default'];
            $updated_time = $this->dateFormatter->format(
              $date_original->getTimestamp(), 'custom', 'H:i', $site_timezone);
          }
          $data->updated_time = $updated_time;
          $service_data = $data;
        }
      }
    }

    return $service_data;
  }

}
