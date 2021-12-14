<?php

namespace Drupal\flexible_weather_api;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Implements WeatherApiPluginInterface.
 */
interface WeatherApiLocationServicePluginInterface extends ConfigurableInterface, PluginFormInterface, PluginInspectionInterface {

  /**
   * Get id of service location plugin.
   */
  public function getServiceId();

  /**
   * Get name of service location plugin.
   */
  public function getServiceName();

  /**
   * Get base_url of service location plugin.
   */
  public function getBaseUrl();

  /**
   * Return json list of geolocation matching $text.
   *
   * @param string $text
   *   The text query for search a place.
   *
   * @return array
   *   An array of matching location.
   */
  public function getAddress(string $text);

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

}
