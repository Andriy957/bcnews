<?php

namespace Drupal\flexible_weather_api\Plugin\WeatherApiLocationServicePlugin;

use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\flexible_weather_api\Service\WeatherApiRequesterInterface;
use Drupal\flexible_weather_api\WeatherApiLocationServicePluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The Google Geocode Api location service.
 *
 * @WeatherApiLocationServicePlugin(
 *   id="weather_api_location_service_google",
 *   label="Google (Geocode Api)",
 *   base_url="https://maps.googleapis.com/maps/api/geocode/json",
 * )
 */
class WeatherApiLocationServiceGoogle extends WeatherApiLocationServicePluginBase {

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
      '#description' => $this->t('You can get API key on the <a href="@location-service-link">@location-service service</a>.', [
        '@location-service-link' => 'https://developers.google.com/maps/documentation/geocoding/get-api-key',
        '@location-service' => $this->t('Google (Geocoding API)'),
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
  public function getAddress(string $text) {
    $language_interface = $this->languageManager->getCurrentLanguage();
    $language = isset($language_interface) ? $language_interface->getId() : 'en';
    $config = $this->getConfiguration();
    $api_key = $config['service_options']['api_key'];
    $query = [
      'address' => $text,
      'language' => $language,
      'key' => $api_key,
    ];
    $service_options = [
      'api_key_required' => TRUE,
      'api_key' => $api_key,
      'query_params' => $query,
      'base_url' => $this->getBaseUrl(),
    ];
    $data = $this->weatherApiRequester->retrieveData($this->getPluginId(), $service_options, FALSE);
    $matches = [];
    if ($data) {
      foreach ($data->results as $result) {
        if (isset($result->address_components) && !empty($result->address_components)) {
          $formatted_address = '';
          foreach ($result->address_components as $component) {
            if (isset($component->types) && $component->types[0] == 'locality') {
              $formatted_address = $component->long_name;
              break;
            }
          }
          $matches[] = [
            'value' => Html::escape($formatted_address),
            'label' => Html::escape($formatted_address),
          ];
        }
      }
    }

    return $matches;
  }

}
