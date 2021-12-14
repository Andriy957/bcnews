<?php

namespace Drupal\weather_api_plugin\Plugin\WeatherApiPlugin;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\flexible_weather_api\Service\WeatherApiRequesterInterface;
use Drupal\weather_api_plugin\WeatherApiPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Weather Stack service.
 *
 * @WeatherApiPlugin(
 *   id="weather_stack_plugin",
 *   label="WeatherStack",
 *   base_url="http://api.weatherstack.com/current",
 *   template="weather_stack_theme",
 * )
 */
class WeatherStack extends WeatherApiPluginBase {

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
   * @param \Drupal\flexible_weather_api\Service\WeatherApiRequesterInterface $weather_api_requester
   *   The weather api requester.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, ConfigFactoryInterface $config_factory, WeatherApiRequesterInterface $weather_api_requester, LanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $config_factory);
    $this->weatherApiRequester = $weather_api_requester;
    $this->languageManager = $language_manager;
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
      $container->get('flexible_weather_api.weather_api_requester'),
      $container->get('language_manager')
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
    $form['service_options']['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API key'),
      '#default_value' => isset($config['service_options']['api_key']) ? $config['service_options']['api_key'] : '',
      '#required' => TRUE,
      '#description' => $this->t('You can get API key on account page in the <a href="@weather-service-link">@weather-service service</a>.', [
        '@weather-service-link' => 'https://weatherstack.com/',
        '@weather-service' => $this->t('Weather Stack'),
      ]),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->setConfiguration([
      'service_options' => [
        'api_key' => $form_state->getValue('api_key'),
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
    $api_key = isset($config['service_options']['api_key']) ? $config['service_options']['api_key'] : NULL;
    $query = [
      'access_key' => $api_key,
      'query' => $city,
      'lang' => $language_id,
      'units' => 'm',
    ];
    $service_options = [
      'api_key_required' => TRUE,
      'api_key' => $api_key,
      'query_params' => $query,
      'base_url' => $this->getBaseUrl(),
      'language' => $language_id,
      'uuid' => $uuid,
    ];

    $weather_details = $this->weatherApiRequester->retrieveData($this->getPluginId(), $service_options, $getFromCache);

    if (!isset($weather_details->error)) {
      return $weather_details;
    }

    return [];
  }

}
