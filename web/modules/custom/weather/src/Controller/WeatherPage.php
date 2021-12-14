<?php

namespace Drupal\weather\Controller;

use Drupal\Core\Render\Markup;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;

/**
 *
 */
class WeatherPage {

  /**
   *
   */
  public function getWeather($city) {
    $client = \Drupal::httpClient();
    $config = \Drupal::config('weather.settings');
    $api_key = $config->get('weather_api_key');

    try {
      $response = $client->get('http://api.openweathermap.org/data/2.5/weather?q=' . $city . ',&appid=' . $api_key . '&units=metric');
      $response_data = $response->getBody();
      $data = json_decode($response_data);
      return [
        '#markup' => Markup::create(
                                     '<h1>' . $data->name . '</h1>' .
                                     '<div>' . 'Temperature: ' . round($data->main->temp) . '°C' . '</div>' .
                                     '<div>' . 'Сloudiness: ' . $data->clouds->all . '%' . '</div>' .
                                     '<div>' . 'Humidity: ' . $data->main->humidity . '%' . '</div>' .
                                     '<div>' . 'Pressure: ' . $data->main->pressure . ' mm' . '</div>' .
                                     '<div>' . 'Wind: ' . $data->wind->speed . ' m/s' . '</div>'
        ),
      ];
    }
    catch (ConnectException $e) {
      return [
        '#markup' => Markup::create('<h1>Internet, DNS, or other connection error</h1>'),
      ];
    }
    catch (RequestException $e) {
      return [
        '#markup' => Markup::create('<h1>City not found</h1>'),
      ];
    }
  }

}
