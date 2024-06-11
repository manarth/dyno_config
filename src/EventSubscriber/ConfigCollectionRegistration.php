<?php

declare(strict_types=1);

namespace Drupal\dyno_config\EventSubscriber;

use Drupal\Core\Config\ConfigCollectionInfo;
use Drupal\Core\Config\ConfigEvents;
use Drupal\dyno_config\Services\DynoConfigManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * A config-collection which stores dynamic config definitions.
 */
class ConfigCollectionRegistration implements EventSubscriberInterface {

  /**
   * Constructor.
   *
   * @param \Drupal\dyno_config\Services\DynoConfigManagerInterface $manager
   *   The manager for dynamic configuration entities.
   */
  public function __construct(protected DynoConfigManagerInterface $manager) {
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ConfigEvents::COLLECTION_INFO][] = ['addCollections'];
    return $events;
  }

  /**
   * {@inheritdoc}
   */
  public function addCollections(ConfigCollectionInfo $collection_info) {
    foreach ($this->getCollections() as $collection) {
      $collection_info->addCollection($collection);
    }
  }

  /**
   * Get a list of all the defined conditional-configuration entities.
   *
   * @return string[]
   *   The config collection names for each conditional-configuration entity.
   */
  protected function getCollections() : array {
    return array_map(fn ($entity) => $entity->getCollectionName(),
      $this->manager->getAll()
    );
  }

}
