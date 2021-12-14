<?php

namespace Drupal\weather_api_entity\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Url;
use Drupal\flexible_weather_api\WeatherApiLocationServicePluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class WeatherServiceForm.
 */
class WeatherServiceForm extends EntityForm {

  /**
   * The weather api location service plugin manager.
   *
   * @var \Drupal\flexible_weather_api\WeatherApiLocationServicePluginManager
   */
  protected $weatherApiLocationServicePluginManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a LocationServicesController object.
   *
   * @param \Drupal\flexible_weather_api\WeatherApiLocationServicePluginManager $weather_api_location_service_plugin_manager
   *   The weather api location service plugin manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(WeatherApiLocationServicePluginManager $weather_api_location_service_plugin_manager, LanguageManagerInterface $language_manager) {
    $this->weatherApiLocationServicePluginManager = $weather_api_location_service_plugin_manager;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.weather_api_location_service_plugin'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $weather_service = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $weather_service->label(),
      '#description' => $this->t("Label for the Weather service."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $weather_service->id(),
      '#machine_name' => [
        'exists' => '\Drupal\weather_api_entity\Entity\WeatherService::load',
      ],
      '#disabled' => !$weather_service->isNew(),
    ];

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Key'),
      '#maxlength' => 255,
      '#default_value' => $weather_service->get('api_key'),
      '#description' => $this->t("API key for the Weather service."),
      '#required' => TRUE,
    ];

    $form['base_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Base URL'),
      '#maxlength' => 255,
      '#default_value' => $weather_service->get('base_url'),
      '#description' => $this->t("base_url for the Weather service."),
      '#required' => TRUE,
    ];

    $form['query'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Query Params'),
      '#maxlength' => 255,
      '#placeholder' => $this->t("param_key=@placeholder\r\nparam_key2=some_value"),
      '#default_value' => $weather_service->get('query'),
      '#description' => $this->t("Specify query params. Example: key=value. Enter one parameter per line. You can use next replacements here: @api_key, @location, @language"),
      '#required' => TRUE,
    ];

    $form['language'] = [
      '#type' => 'select',
      '#title' => $this->t('Language'),
      '#options' => $this->getAvailableSiteLanguages(),
      '#empty_option' => $this->t('- Please select -'),
      '#default_value' => $weather_service->get('language'),
      '#required' => TRUE,
    ];

    $form['location_service'] = [
      '#type' => 'select',
      '#title' => $this->t('Select available location service'),
      '#options' => $this->getAvailableWeatherLocationServices(),
      '#empty_option' => $this->t('- Please select -'),
      '#default_value' => $weather_service->get('location_service'),
      '#required' => TRUE,
    ];

    $this->buildLocationAutocompleteField($form, $form_state);

    $form['template'] = [
      '#type' => 'select',
      '#title' => $this->t('Select available template'),
      '#options' => weather_api_entity_available_templates(),
      '#empty_option' => $this->t('- Please select -'),
      '#default_value' => $weather_service->get('template'),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $weather_service = $this->entity;
    $status = $weather_service->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Weather service.', [
          '%label' => $weather_service->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Weather service.', [
          '%label' => $weather_service->label(),
        ]));
    }
    $form_state->setRedirectUrl($weather_service->toUrl('collection'));
  }

  /**
   * Helper function to getting available location services.
   */
  public function getAvailableWeatherLocationServices() {
    $plugins = [];
    foreach ($this->weatherApiLocationServicePluginManager->getDefinitions() as $plugin_id => $plugin) {
      $plugins[$plugin_id] = $plugin['label'];
    }

    return $plugins;
  }

  /**
   * Helper function to getting available location services.
   */
  public function getAvailableSiteLanguages() {
    $languages = $this->languageManager->getLanguages();
    $options = [];
    foreach ($languages as $language_id => $language) {
      $options[$language_id] = $language->getName();;
    }

    return $options;
  }

  /**
   * Build the location service autocomplete field.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function buildLocationAutocompleteField(array &$form, FormStateInterface $form_state) {
    $weather_service = $this->entity;
    $form['location_service_autocomplete'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => ['location-service-autocomplete'],
      ],
    ];

    foreach ($this->weatherApiLocationServicePluginManager->getDefinitions() as $plugin_id => $plugin) {
      $form['location_service_autocomplete'][$plugin_id] = [
        '#type' => 'textfield',
        '#title' => $this->t('City'),
        '#description' => $this->t('For enabling autocomplete you should add required credentials for selected <a href="@plugin-id-link">location service</a>.', [
          '@plugin-id-link' => Url::fromRoute('flexible_weather_api.weather_api_location_service_plugin_configuration', ['location_service' => $plugin_id])->toString(),
        ]),
        '#autocomplete_route_name' => 'flexible_weather_api.location_service.' . $plugin_id,
        '#autocomplete_route_parameters' => ['plugin_id' => $plugin_id],
        '#maxlength' => 255,
        '#states' => [
          'visible' => [
            'select[name="location_service"]' => ['value' => $plugin_id],
          ],
          'required' => [
            'select[name="location_service"]' => ['value' => $plugin_id],
          ],
        ],
      ];

      if ($weather_service->get('location_service') == $plugin_id) {
        $form['location_service_autocomplete'][$plugin_id]['#default_value'] = $weather_service->get('address');
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function copyFormValuesToEntity(EntityInterface $entity, array $form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    if ($this->entity instanceof EntityWithPluginCollectionInterface) {
      // Do not manually update values represented by plugin collections.
      $values = array_diff_key($values, $this->entity->getPluginCollections());
    }

    foreach ($values as $key => $value) {
      $entity->set($key, $value);
    }

    // Set address from selected location service.
    $entity->set('address', $values[$values['location_service']]);
  }

}
