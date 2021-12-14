<?php

namespace Drupal\weather_api_entity\Entity;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Defines the Google tag container manager.
 */
class WeatherServiceManager implements WeatherServiceManagerInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The mock entity query.
   *
   * @var \Drupal\Core\Entity\Query\QueryInterface
   */
  protected $entityQuery;

  /**
   * Constructs a ContainerManager.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;

  }

  /**
   * {@inheritdoc}
   */
  public function getWeatherServiceIds() {
    $query = $this->entityTypeManager->getStorage('weather_service')->getQuery();

    return $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function getWeatherServiceEntities() {
    return $this->entityTypeManager->getStorage('weather_service')
      ->loadMultiple($this->getWeatherServiceIds());
  }

  /**
   * {@inheritdoc}
   */
  public function getWeatherServiceEntityById(string $id) {
    return $this->entityTypeManager->getStorage('weather_service')->load($id);
  }

  /**
   * {@inheritdoc}
   */
  public function build(WeatherServiceInterface $weather_entity, bool $getFromCache = TRUE) {
    return [
      '#theme' => $weather_entity->get('template'),
      '#weather_details' => $weather_entity->getWeatherDetails($weather_entity, $getFromCache),
    ];
  }

}
