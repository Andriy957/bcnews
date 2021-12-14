<?php

namespace Drupal\flexible_weather_api\Form;

use Drupal\Core\Form\FormBase;
use Drupal\flexible_weather_api\WeatherApiLocationServicePluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\Form\FormStateInterface;

/**
 * The plugin configuration form.
 */
class LocationServiceConfigurationForm extends FormBase {

  /**
   * The location service plugin manager.
   *
   * @var \Drupal\flexible_weather_api\WeatherApiLocationServicePluginManager
   */
  protected $locationServiceManager;

  /**
   * The plugin instance being configured.
   *
   * @var \Drupal\flexible_weather_api\WeatherApiLocationServicePluginInterface
   */
  protected $plugin;

  /**
   * Constructs a new form object.
   *
   * @param \Drupal\flexible_weather_api\WeatherApiLocationServicePluginManager $location_service_manager
   *   The location service plugin manager.
   */
  public function __construct(WeatherApiLocationServicePluginManager $location_service_manager) {
    $this->locationServiceManager = $location_service_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.weather_api_location_service_plugin')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'weather_api_location_service_plugin_configuration';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $location_service = NULL) {
    if ($this->locationServiceManager->getDefinition($location_service)) {
      $this->plugin = $this->locationServiceManager->createInstance($location_service);
      $form += $this->plugin->buildConfigurationForm($form, $form_state);

      $form['submit'] = [
        '#type' => 'submit',
        '#value' => 'Save',
      ];

      return $form;
    }
    else {
      throw new NotFoundHttpException();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->plugin->submitConfigurationForm($form, $form_state);
  }

}
