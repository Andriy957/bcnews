<?php

namespace Drupal\weather_api_plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Implements WeatherApiPluginInterface.
 */
interface WeatherApiPluginInterface extends PluginInspectionInterface, PluginFormInterface {

  /**
   * Gets default configuration for this plugin.
   *
   * @return array
   *   An associative array with the default configuration.
   */
  public function defaultConfiguration();

  /**
   * Gets this plugin's configuration.
   *
   * @return array
   *   An array of this plugin's configuration.
   */
  public function getConfiguration();

  /**
   * Sets the configuration for this plugin instance.
   *
   * @param array $configuration
   *   An associative array containing the plugin's configuration.
   */
  public function setConfiguration(array $configuration);

  /**
   * Build the plugin configuration form.
   *
   * @param array $form
   *   An associative array containing the initial structure of the plugin form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   Return configuration form.
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state);

  /**
   * Form validation handler.
   *
   * @param array $form
   *   An associative array containing the structure of the plugin form as built
   *   by static::buildConfigurationForm().
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state);

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the plugin form as built
   *   by static::buildConfigurationForm().
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state);

  /**
   * Get the renderable template for weather details.
   */
  public function getBuildTemplate();

  /**
   * Get the plugin label.
   */
  public function getPluginLabel();

  /**
   * Get base_url of service location plugin.
   */
  public function getBaseUrl();

  /**
   * Ger weather details.
   *
   * @param string $city
   *   The city.
   * @param string $uuid
   *   The unique uud code for caching weather data.
   * @param bool $getFromCache
   *   Bool value for getting data from cache.
   */
  public function getWeatherDetails(string $city, string $uuid, bool $getFromCache);

  /**
   * Create renderable array of weather details.
   *
   * @param string $city
   *   The city.
   * @param string $uuid
   *   The unique uud code for caching weather data.
   * @param bool $getFromCache
   *   Bool value for getting data from cache.
   */
  public function build(string $city, string $uuid, bool $getFromCache = TRUE);

  /**
   * Get id of a weather service plugin.
   */
  public function getServiceId();

}
