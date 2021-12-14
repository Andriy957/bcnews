<?php

namespace Drupal\weather_api_entity\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\RendererInterface;
use Drupal\weather_api_entity\Entity\WeatherServiceManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * RefreshWeatherBlockController for the weather_api_entity module.
 */
class RefreshWeatherBlockController extends ControllerBase {

  /**
   * The weather service manager.
   *
   * @var \Drupal\weather_api_entity\Entity\WeatherServiceManager
   */
  protected $weatherServiceManager;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a RefreshWeatherBlockController object.
   *
   * @param \Drupal\weather_api_entity\Entity\WeatherServiceManager $weather_service_manager
   *   The weather service manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(WeatherServiceManager $weather_service_manager, RendererInterface $renderer) {
    $this->weatherServiceManager = $weather_service_manager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('weather_api_entity.weather_service_manager'),
      $container->get('renderer')
    );
  }

  /**
   * Callback method for route weather_api_entity.weather_api_ajax_refresh.
   *
   * Ajax update the block with the weather details.
   *
   * @param string $weather_service_id
   *   The weather service id.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Return updated block.
   */
  public function ajaxRefreshWeatherData(string $weather_service_id) {
    $response = new AjaxResponse();
    $weather_entity = $this->weatherServiceManager->getWeatherServiceEntityById($weather_service_id);
    $build = $this->weatherServiceManager->build($weather_entity, FALSE);
    $build = $this->renderer->render($build);
    $response->addCommand(new ReplaceCommand('.block-uuid-' . $weather_entity->uuid(), $build));

    return $response;
  }

}
