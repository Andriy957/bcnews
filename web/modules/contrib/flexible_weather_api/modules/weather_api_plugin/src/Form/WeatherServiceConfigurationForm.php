<?php

namespace Drupal\weather_api_plugin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\weather_api_plugin\WeatherApiPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\Form\FormStateInterface;

/**
 * The plugin configuration form.
 */
class WeatherServiceConfigurationForm extends FormBase {

  /**
   * The weather service plugin manager.
   *
   * @var \Drupal\weather_api_plugin\WeatherApiPluginManager
   */
  protected $weatherServiceManager;

  /**
   * The plugin instance being configured.
   *
   * @var \Drupal\weather_api_plugin\WeatherApiPluginManager
   */
  protected $plugin;

  /**
   * Constructs a new form object.
   *
   * @param \Drupal\weather_api_plugin\WeatherApiPluginManager $weather_service_manager
   *   The location service plugin manager.
   */
  public function __construct(WeatherApiPluginManager $weather_service_manager) {
    $this->weatherServiceManager = $weather_service_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.weather_api_plugin')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'weather_service_plugin_configuration';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $weather_service = NULL) {
    if ($this->weatherServiceManager->getDefinition($weather_service)) {
      $this->plugin = $this->weatherServiceManager->createInstance($weather_service);
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
