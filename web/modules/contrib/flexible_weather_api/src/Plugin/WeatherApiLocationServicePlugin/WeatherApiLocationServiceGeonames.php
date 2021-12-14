<?php

namespace Drupal\flexible_weather_api\Plugin\WeatherApiLocationServicePlugin;

use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\flexible_weather_api\WeatherApiLocationServicePluginBase;
use GeoNames\Client as GeoNamesClient;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The Geonames location service.
 *
 * @WeatherApiLocationServicePlugin(
 *   id="weather_api_location_service_geonames",
 *   label="Geonames",
 * )
 */
class WeatherApiLocationServiceGeonames extends WeatherApiLocationServicePluginBase {

  use StringTranslationTrait;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

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
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, ConfigFactoryInterface $config_factory, LanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $config_factory);
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
    $form['service_options']['username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#default_value' => isset($config['service_options']['username']) ? $config['service_options']['username'] : '',
      '#required' => TRUE,
      '#description' => $this->t('You should register on the <a href="@location-service-link">@location-service service</a> and using your login for this service.', [
        '@location-service-link' => 'https://www.geonames.org/',
        '@location-service' => $this->t('Geonames'),
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
        'username' => $form_state->getValue('username'),
      ],
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getAddress(string $text) {
    $config = $this->getConfiguration();
    $username = $config['service_options']['username'];
    $geonames_client = new GeoNamesClient($username);
    $language_id = $this->languageManager->getCurrentLanguage()->getId();
    $addresses = $geonames_client->search([
      'q' => $text,
      'lang' => $language_id,
      'maxRows' => 10,
    ]);
    $matches = [];
    foreach ($addresses as $address) {
      $matches[] = [
        'value' => Html::escape($address->name),
        'label' => Html::escape($address->name),
      ];
    }

    return $matches;
  }

}
