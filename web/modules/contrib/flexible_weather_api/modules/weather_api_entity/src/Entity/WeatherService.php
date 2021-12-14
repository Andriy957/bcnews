<?php

namespace Drupal\weather_api_entity\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Weather service entity.
 *
 * @ConfigEntityType(
 *   id = "weather_service",
 *   label = @Translation("Weather service Entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\weather_api_entity\WeatherServiceListBuilder",
 *     "form" = {
 *       "add" = "Drupal\weather_api_entity\Form\WeatherServiceForm",
 *       "edit" = "Drupal\weather_api_entity\Form\WeatherServiceForm",
 *       "delete" = "Drupal\weather_api_entity\Form\WeatherServiceDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\weather_api_entity\WeatherServiceHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "weather_service",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "api_key",
 *     "base_url",
 *     "query",
 *     "language",
 *     "location_service",
 *     "address",
 *     "template"
 *   },
 *   links = {
 *     "canonical" = "/admin/config/services/weather_service_entity/{weather_service}",
 *     "add-form" = "/admin/config/services/weather_service_entity/add",
 *     "edit-form" = "/admin/config/services/weather_service_entity/{weather_service}/edit",
 *     "delete-form" = "/admin/config/services/weather_service_entity/{weather_service}/delete",
 *     "collection" = "/admin/config/services/weather_service_entity"
 *   }
 * )
 */
class WeatherService extends ConfigEntityBase implements WeatherServiceInterface {

  /**
   * The Weather service ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Weather service label.
   *
   * @var string
   */
  protected $label;

  /**
   * The Weather service API Key.
   *
   * @var string
   */
  protected $api_key;

  /**
   * The base URL for request.
   *
   * @var string
   */
  protected $base_url;

  /**
   * The required query params.
   *
   * @var string
   */
  protected $query;

  /**
   * The language.
   *
   * @var string
   */
  protected $language;

  /**
   * The location service.
   *
   * @var string
   */
  protected $location_service;

  /**
   * The address for request.
   *
   * @var string
   */
  protected $address;


  /**
   * The template for displaying weather data.
   *
   * @var string
   */
  protected $template;

  /**
   * {@inheritdoc}
   */
  public function getWeatherDetails(WeatherServiceInterface $weather_entity, bool $getFromCache) {
    $weather_api_requester = \Drupal::service('flexible_weather_api.weather_api_requester');
    $language_id = $weather_entity->get('language');
    $api_key = $weather_entity->get('api_key');

    $query = $this->buildQueryParams($weather_entity);
    $service_options = [
      'api_key_required' => TRUE,
      'api_key' => $api_key,
      'query_params' => $query,
      'base_url' => $weather_entity->get('base_url'),
      'language' => $language_id,
      'uuid' => $weather_entity->get('uuid'),
    ];

    $weather_details = $weather_api_requester->retrieveData($weather_entity->id(), $service_options, $getFromCache);

    if (!isset($weather_details->error)) {
      return $weather_details;
    }

    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function buildQueryParams(WeatherServiceInterface $weatherService) {
    return str_replace(
      ['@api_key', '@location', '@language'],
      [
        $weatherService->get('api_key'),
        $weatherService->get('address'),
        $weatherService->get('language'),
      ],
      $weatherService->get('query')
    );
  }

}
