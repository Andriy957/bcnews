<?php

namespace Drupal\weather_api_entity\Entity;

/**
 * Provides an interface for a WeatherService manager.
 */
interface WeatherServiceManagerInterface {

  /**
   * Get weather service entity Ids.
   *
   * @return array
   *   The entity Id array.
   */
  public function getWeatherServiceIds();

  /**
   * Get all available weather service entities.
   *
   * @return array
   *   Return array of weather service entities.
   */
  public function getWeatherServiceEntities();

  /**
   * Retrieve the weather service entity.
   *
   * @param string $id
   *   The weather entity id.
   *
   * @return mixed
   *   Return loaded weather service entity.
   */
  public function getWeatherServiceEntityById(string $id);

  /**
   * Create the render array of details from weather service.
   *
   * @param WeatherServiceInterface $weather_entity
   *   The weather service entity.
   * @param bool $getFromCache
   *   The bool value for retrieve weather data from cache.
   *
   * @return array
   *   Return the render array of weather details.
   */
  public function build(WeatherServiceInterface $weather_entity, bool $getFromCache = TRUE);

}
