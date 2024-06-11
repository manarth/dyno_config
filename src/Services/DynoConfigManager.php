<?php

declare(strict_types=1);

namespace Drupal\dyno_config\Services;

use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\dyno_config\Entity\DynoConfigInterface;

/**
 * Manager for dynamic configuration entities.
 */
class DynoConfigManager implements DynoConfigManagerInterface {

  /**
   * Storage service for dynamic configuration entities.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected EntityStorageInterface $storage;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $etm
   *   The entity-type manager service.
   * @param \Drupal\Core\Config\StorageInterface $config
   *   The config storage service, used to create collections.
   */
  public function __construct(EntityTypeManagerInterface $etm, protected StorageInterface $config) {
    $this->storage = $etm->getStorage('dyno_config');
  }

  /**
   * {@inheritdoc}
   */
  public function getAll() : array {
    $result = $this
      ->storage
      ->loadMultiple();
    uasort($result, [self::class, 'sortByPriority']);
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getActive() : array {
    $result = $this
      ->storage
      ->loadByProperties([
        'status' => DynoConfigInterface::ENABLED,
      ]);
    uasort($result, [self::class, 'sortByPriority']);
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function get(string $entityId) : ?DynoConfigInterface {
    return $this
      ->storage
      ->load($entityId);
  }

  /**
   * {@inheritdoc}
   */
  public function getCollectionFor(string $entityId) : StorageInterface {
    return $this->config->createCollection($this->get($entityId)->getCollectionName());
  }

  /**
   * Sort callback to sort entities by priority in the config override.
   *
   * @param \Drupal\dyno_config\Entity\DynoConfigInterface $a
   *   The entity to compare against.
   * @param \Drupal\dyno_config\Entity\DynoConfigInterface $b
   *   The comparitor.
   *
   * @return int
   *   The result of the <=> operator.
   *
   * @SuppressWarnings(PHPMD.ShortVariable)
   */
  protected static function sortByPriority(DynoConfigInterface $a, DynoConfigInterface $b) {
    return $a->getPriority() <=> $b->getPriority();
  }

}
