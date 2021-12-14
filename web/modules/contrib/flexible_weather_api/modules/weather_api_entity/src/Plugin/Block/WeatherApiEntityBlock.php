<?php

namespace Drupal\weather_api_entity\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\weather_api_entity\Entity\WeatherServiceManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block to display the weather.
 *
 * @Block(
 *   id = "weather_api_entity_block",
 *   admin_label = @Translation("Weather Api Entity Block"),
 * )
 */
class WeatherApiEntityBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The weather service manager.
   *
   * @var \Drupal\weather_api_entity\Entity\WeatherServiceManagerInterface
   */
  protected $weatherServiceManager;

  /**
   * Constructs a RefreshWeatherBlockController object.
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
   * @param \Drupal\weather_api_entity\Entity\WeatherServiceManagerInterface $weather_service_manager
   *   The weather service manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, WeatherServiceManagerInterface $weather_service_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->weatherServiceManager = $weather_service_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('weather_api_entity.weather_service_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $config = $this->getConfiguration();
    $weather_entity = $this->weatherServiceManager->getWeatherServiceEntityById($config['weather_entity']);

    if (!$weather_entity) {
      return $build;
    }

    $build = $this->weatherServiceManager->build($weather_entity);
    if (!is_object($build['#weather_details']) && isset($build['#weather_details']['error'])) {
      return [
        '#markup' => $build['#weather_details']['error'],
      ];
    }
    if (is_object($build['#weather_details'])) {
      $build['#weather_details']->plugin_id = $weather_entity->id();
      $build['#weather_details']->uuid = $weather_entity->uuid();
      $build['#weather_details']->refresh_link = [
        '#title' => $this->t('Refresh'),
        '#type' => 'link',
        '#url' => Url::fromRoute('weather_api_entity.weather_api_ajax_refresh', [
          'weather_service_id' => $weather_entity->id(),
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

    $weather_entities = $this->getAvailableWeatherEntities();
    $form['weather_entity'] = [
      '#type' => 'select',
      '#title' => $this->t('Select available weather service'),
      '#options' => $weather_entities,
      '#empty_option' => $this->t('- Please select -'),
      '#default_value' => isset($config['weather_entity']) ? $config['weather_entity'] : '',
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('weather_entity', $form_state->getValue('weather_entity'));
  }

  /**
   * Helper function to getting available weather services.
   */
  public function getAvailableWeatherEntities() {
    $entities = [];
    foreach ($this->weatherServiceManager->getWeatherServiceEntities() as $entity_id => $entity) {
      $entities[$entity_id] = $entity->label();
    }

    return $entities;
  }

}
