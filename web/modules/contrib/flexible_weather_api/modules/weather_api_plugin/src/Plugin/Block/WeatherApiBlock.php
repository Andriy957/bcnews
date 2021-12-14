<?php

namespace Drupal\weather_api_plugin\Plugin\Block;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\weather_api_plugin\WeatherApiPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block to display the weather.
 *
 * @Block(
 *   id = "weather_api_block",
 *   deriver = "Drupal\weather_api_plugin\Plugin\Derivative\LocationServiceBlockDeriver",
 * )
 */
class WeatherApiBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The weather api plugin manager.
   *
   * @var \Drupal\weather_api_plugin\WeatherApiPluginManager
   */
  protected $weatherApiPluginManager;

  /**
   * The UUID service.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidService;

  /**
   * Constructs a WeatherServicesController object.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\weather_api_plugin\WeatherApiPluginManager $weather_api_plugin_manager
   *   The weather api plugin manager.
   * @param \Drupal\Component\Uuid\UuidInterface $uuid_service
   *   The UUID service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, WeatherApiPluginManager $weather_api_plugin_manager, UuidInterface $uuid_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->weatherApiPluginManager = $weather_api_plugin_manager;
    $this->uuidService = $uuid_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.weather_api_plugin'),
      $container->get('uuid')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $config = $this->getConfiguration();
    $plugin_id = $this->getDerivativeId();
    try {
      $instance = $this->weatherApiPluginManager->createInstance($config['weather_plugin']);
    }
    catch (PluginException $e) {
    }

    if (!isset($instance)) {
      return $build;
    }

    $build = $instance->build($config[$plugin_id], $config['block_uuid_cache']);

    if (!is_object($build['#weather_details']) && isset($build['#weather_details']['error'])) {
      return [
        '#markup' => $build['#weather_details']['error'],
      ];
    }
    if (is_object($build['#weather_details'])) {
      $build['#weather_details']->plugin_id = $config['weather_plugin'];
      $build['#weather_details']->uuid = $config['block_uuid_cache'];
      $build['#weather_details']->refresh_link = [
        '#title' => $this->t('Refresh'),
        '#type' => 'link',
        '#url' => Url::fromRoute('weather_api_plugin.weather_api_ajax_refresh', [
          'city' => $config[$plugin_id],
          'plugin_id' => $config['weather_plugin'],
          'uuid' => $config['block_uuid_cache'],
        ]),
        '#attributes' => [
          'class' => ['use-ajax'],
        ],
        '#attached' => [
          'library' => ['core/drupal.ajax'],
        ],
      ];
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    $plugin_id = $this->getDerivativeId();
    $form[$plugin_id] = [
      '#type' => 'textfield',
      '#title' => $this->t('City'),
      '#description' => $this->t('For enabling autocomplete you should add required credentials for selected <a href="@plugin-id-link">location service</a>.', [
        '@plugin-id-link' => Url::fromRoute('flexible_weather_api.weather_api_location_service_plugin_configuration', ['location_service' => $plugin_id])->toString(),
      ]),
      '#autocomplete_route_name' => 'flexible_weather_api.location_service.' . $plugin_id,
      '#autocomplete_route_parameters' => ['plugin_id' => $plugin_id],
      '#maxlength' => 255,
      '#default_value' => isset($config[$plugin_id]) ? $config[$plugin_id] : '',
      '#required' => TRUE,
    ];

    $weather_plugins = $this->getAvailableWeatherServices();
    $form['weather_plugin'] = [
      '#type' => 'select',
      '#title' => $this->t('Select available weather service'),
      '#options' => $weather_plugins,
      '#empty_option' => $this->t('- Please select -'),
      '#default_value' => isset($config['weather_plugin']) ? $config['weather_plugin'] : '',
      '#required' => TRUE,
    ];

    $form['block_uuid_cache'] = [
      '#type' => 'hidden',
      '#value' => isset($config['block_uuid_cache']) ? $config['block_uuid_cache'] : $this->generateUuidCache(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $plugin_id = $this->getDerivativeId();
    $this->setConfigurationValue($plugin_id, $form_state->getValue($plugin_id));
    $this->setConfigurationValue('weather_plugin', $form_state->getValue('weather_plugin'));
    $this->setConfigurationValue('block_uuid_cache', $form_state->getValue('block_uuid_cache'));
  }

  /**
   * Helper function to getting available weather services.
   */
  public function getAvailableWeatherServices() {
    $plugins = [];
    foreach ($this->weatherApiPluginManager->getDefinitions() as $plugin_id => $plugin) {
      $plugins[$plugin_id] = $plugin['label'];
    }

    return $plugins;
  }

  /**
   * Generate unique uuid for block. Required for set weather caching.
   */
  public function generateUuidCache() {
    return $this->uuidService->generate();
  }

}
