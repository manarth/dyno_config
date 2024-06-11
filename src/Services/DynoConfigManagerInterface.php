<?php

declare(strict_types=1);

namespace Drupal\dyno_config\Services;

use Drupal\Core\Config\StorageInterface;
use Drupal\dyno_config\Entity\DynoConfigInterface;

/**
 * Manager for dynamic configuration entities.
 */
interface DynoConfigManagerInterface {

  /**
   * Fetch all the dynamic configuration entities.
   *
   * @return \Drupal\dyno_config\Entity\DynoConfigInterface[]
   *   Array of dynamic configuration entities.
   */
  public function getAll() : array;

  /**
   * Fetch all the enabled dynamic configuration entities.
   *
   * @return \Drupal\dyno_config\Entity\DynoConfigInterface[]
   *   Array of enabled dynamic configuration entities.
   */
  public function getActive() : array;

  /**
   * Fetch a single dynamic configuration entities.
   *
   * @param string $id
   *   The ID of the entity to fetch.
   *
   * @return \Drupal\dyno_config\Entity\DynoConfigInterface|null
   *   If the ID is valid, the dynamic configuration entity is returned.
   */
  public function get(string $id) : ?DynoConfigInterface;

  /**
   * Get the collection which stores configuration for a specific entity.
   *
   * @param string $entityId
   *   The ID of the dynamic configuration entity.
   *
   * @return \Drupal\Core\Config\StorageInterface
   *   The storage collection.
   */
  public function getCollectionFor(string $entityId) : StorageInterface;

}
