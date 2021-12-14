<?php

/**
 * @file
 * Hooks specific to the weather_api_entity module.
 */

/**
 * Allow you to alter available templates.
 *
 * @param array $available_templates
 *   Available templates which you can alter.
 */
function hook_weather_api_entity_available_templates_alter(array &$available_templates) {
  $available_templates['new_weather_service_entity_theme'] = 'New weather service';
}
