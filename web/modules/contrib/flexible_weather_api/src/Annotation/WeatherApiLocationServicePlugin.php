<?php

namespace Drupal\flexible_weather_api\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a WeatherApiLocationServicePlugin item annotation object.
 *
 * @see \Drupal\flexible_weather_api\WeatherApiLocationServicePluginManager
 * @see plugin_api
 *
 * @Annotation
 */
class WeatherApiLocationServicePlugin extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the location service.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The base api URL.
   *
   * @var string
   */
  public $baseUrl;

}
