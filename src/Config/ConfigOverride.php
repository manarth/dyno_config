<?php

declare(strict_types=1);

namespace Drupal\dyno_config\Config;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorableConfigBase;
use Drupal\Core\Config\StorageInterface;
use Drupal\dyno_config\Entity\DynoConfigInterface;

/**
 * Implementation of the core Config Override API.
 */
class ConfigOverride implements ConfigFactoryOverrideInterface {

  /**
   * Constructor.
   *
   * @param \Drupal\dyno_config\Entity\DynoConfigInterface $entity
   *   The entity which defines the dynamic storage.
   * @param \Drupal\Core\Config\StorageInterface $collection
   *   The config storage for this particular entity.
   */
  public function __construct(
    protected DynoConfigInterface $entity,
    protected StorageInterface $collection,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names) : array {
    $overrides = $this
      ->collection
      ->readMultiple($names);
    if ($overrides) {
      if ($this->entity->isEnabled() && $this->entity->evaluate()) {
        return $overrides;
      }
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix() : string {
    return 'DynoConfig';
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($name) : CacheableMetadata {
    // @todo Load the cache contexts from the entity conditions.
    $cache = new CacheableMetadata();
    return $cache;
  }

  /**
   * {@inheritdoc}
   */
  public function createConfigObject($name, $collection = StorageInterface::DEFAULT_COLLECTION) : ?StorableConfigBase {
    return NULL;
  }

}
