<?php

namespace Drupal\weather_api_plugin\Plugin\WeatherApiPlugin;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\weather_api_plugin\WeatherApiPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Yahoo service.
 *
 * @WeatherApiPlugin(
 *   id="yahoo_weather_plugin",
 *   label="YahooWeather",
 *   base_url="https://weather-ydn-yql.media.yahoo.com/forecastrss",
 *   template="yahoo_weather_theme",
 * )
 */
class YahooWeather extends WeatherApiPluginBase {

  use StringTranslationTrait;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The weather API requester.
   *
   * @var \Drupal\flexible_weather_api\Service\WeatherApiRequesterInterface
   */
  protected $weatherApiRequester;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The cache backend to be used.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Constructs an ImageToolkitBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   The cache backend to be used.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              array $plugin_definition,
                              ConfigFactoryInterface $config_factory,
                              LanguageManagerInterface $language_manager,
                              CacheBackendInterface $cache_backend,
                              TimeInterface $time,
                              DateFormatterInterface $date_formatter) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $config_factory);
    $this->languageManager = $language_manager;
    $this->cacheBackend = $cache_backend;
    $this->time = $time;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('language_manager'),
      $container->get('cache.discovery'),
      $container->get('datetime.time'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'service_options' => [],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    $form['service_options']['api_information'] = [
      '#markup' => $this->t('You can get all required parameters on the your APP page <a href="@weather-service-link">@weather-service service</a>.', [
        '@weather-service-link' => 'https://developer.yahoo.com/weather/',
        '@weather-service' => $this->t('Yahoo Weather API'),
      ]),
    ];

    $form['service_options']['app_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('APP Id'),
      '#default_value' => isset($config['service_options']['app_id']) ? $config['service_options']['app_id'] : '',
      '#required' => TRUE,
    ];

    $form['service_options']['consumer_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Consumer key'),
      '#default_value' => isset($config['service_options']['consumer_key']) ? $config['service_options']['consumer_key'] : '',
      '#required' => TRUE,
    ];

    $form['service_options']['consumer_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Consumer secret'),
      '#default_value' => isset($config['service_options']['consumer_secret']) ? $config['service_options']['consumer_secret'] : '',
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->setConfiguration([
      'service_options' => [
        'app_id' => $form_state->getValue('app_id'),
        'consumer_key' => $form_state->getValue('consumer_key'),
        'consumer_secret' => $form_state->getValue('consumer_secret'),
        'curl_request' => $form_state->getValue('curl_request'),
      ],
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function build(string $city, string $uuid, bool $getFromCache = TRUE) {
    return [
      '#theme' => $this->getBuildTemplate(),
      '#weather_details' => $this->getWeatherDetails($city, $uuid, $getFromCache),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getWeatherDetails(string $city, string $uuid, bool $getFromCache) {
    $config = $this->getConfiguration();
    $language_id = $this->languageManager->getCurrentLanguage()->getId();

    $app_id = isset($config['service_options']['app_id']) ? $config['service_options']['app_id'] : NULL;
    $consumer_key = isset($config['service_options']['consumer_key']) ? $config['service_options']['consumer_key'] : NULL;
    $consumer_secret = isset($config['service_options']['consumer_secret']) ? $config['service_options']['consumer_secret'] : NULL;

    $query_params = [
      'location' => $city,
      'u' => 'c',
      'format' => 'json',
    ];

    $oauth = [
      'oauth_consumer_key' => $consumer_key,
      'oauth_nonce' => uniqid(mt_rand(1, 1000)),
      'oauth_signature_method' => 'HMAC-SHA1',
      'oauth_timestamp' => time(),
      'oauth_version' => '1.0',
    ];

    $base_info = $this->buildBaseString($this->getBaseUrl(), 'GET', array_merge($query_params, $oauth));
    $composite_key = rawurlencode($consumer_secret) . '&';
    $oauth_signature = base64_encode(hash_hmac('sha1', $base_info, $composite_key, TRUE));
    $oauth['oauth_signature'] = $oauth_signature;

    $headers = [
      $this->buildAuthorizationHeader($oauth),
      'X-Yahoo-App-Id: ' . $app_id,
    ];

    $service_options = [
      'query_params' => $query_params,
      'headers' => $headers,
      'base_url' => $this->getBaseUrl(),
      'language' => $language_id,
      'uuid' => $uuid,
    ];

    $weather_details = $this->retrieveData($this->getPluginId(), $service_options, $getFromCache);

    if (!isset($weather_details->error)) {
      return $weather_details;
    }

    return [];
  }

  /**
   * Helper function for building base string.
   *
   * @param string $baseURI
   *   The base url.
   * @param string $method
   *   HTTP method.
   * @param array $params
   *   The params.
   *
   * @return string
   *   Return built string.
   */
  protected function buildBaseString(string $baseURI, string $method, array $params) {
    $r = [];
    ksort($params);
    foreach ($params as $key => $value) {
      $r[] = "$key=" . rawurlencode($value);
    }
    return $method . "&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $r));
  }

  /**
   * Helper function for building OAuth header.
   *
   * @param array $oauth
   *   OAuth params.
   *
   * @return string
   *   Return built string.
   */
  protected function buildAuthorizationHeader(array $oauth) {
    $r = 'Authorization: OAuth ';
    $values = [];
    foreach ($oauth as $key => $value) {
      $values[] = "$key=\"" . rawurlencode($value) . "\"";
    }
    $r .= implode(', ', $values);
    return $r;
  }

  /**
   * Cache handling.
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
  public function retrieveData(string $plugin_id, array $service_options, $getFromCache = TRUE, $method = 'GET') {
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
      $data = $this->requestData($service_options);
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
   * @param array $service_options
   *   The options for http request.
   *   Can contains:
   *   - api_key,
   *   - api_key_required,
   *   - query_params,
   *   - base_url.
   *
   * @return array
   *   Return response from the service.
   */
  private function requestData(array $service_options) {
    $service_data = [];
    $options = [
      CURLOPT_HTTPHEADER => $service_options['headers'],
      CURLOPT_HEADER => FALSE,
      CURLOPT_URL => $this->getBaseUrl() . '?' . http_build_query($service_options['query_params']),
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_SSL_VERIFYPEER => FALSE,
    ];

    $ch = curl_init();
    curl_setopt_array($ch, $options);
    $response = curl_exec($ch);
    curl_close($ch);
    if (isset($response)) {
      $data = json_decode($response);
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

    return $service_data;
  }

}
