<?php

namespace Drupal\weather_api_plugin\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a WeatherApiPlugin item annotation object.
 *
 * @see \Drupal\weather_api_plugin\WeatherApiPluginManager
 * @see plugin_api
 *
 * @Annotation
 */
class WeatherApiPlugin extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
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

  /**
   * The renderable template.
   *
   * @var string
   */
  public $template;

  /**
   * The overridden label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $overriddenLabel;

}
