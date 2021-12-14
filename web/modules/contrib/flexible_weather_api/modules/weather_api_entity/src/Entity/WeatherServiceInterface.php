<?php

namespace Drupal\weather_api_entity\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Weather service entities.
 */
interface WeatherServiceInterface extends ConfigEntityInterface {

  /**
   * The method is using for retrieve weather data from external service.
   *
   * @param WeatherServiceInterface $weather_entity
   *   The weather service entity with request params.
   * @param bool $getFromCache
   *   The bool value for retrieve weather data from cache.
   *
   * @return mixed
   *   Return the weather details.
   */
  public function getWeatherDetails(WeatherServiceInterface $weather_entity, bool $getFromCache);

}
