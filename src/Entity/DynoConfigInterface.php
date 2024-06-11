<?php

declare(strict_types=1);

namespace Drupal\dyno_config\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * A Dynamic Configuration entity defines dynamic run-time configuration.
 */
interface DynoConfigInterface extends ConfigEntityInterface {

  /**
   * The dynamic configuration entity is not enabled.
   */
  const DISABLED = 0;

  /**
   * The dynamic configuration entity is enabled.
   */
  const ENABLED = 1;

  /**
   * The collection name is this prefix followed by the entity ID.
   */
  const COLLECTION_NAME_PREFIX = 'dyno_config.';

  /**
   * Get the name used to identify the config collection for this entity.
   *
   * @return string
   *   The collection name.
   */
  public function getCollectionName() : string;

  /**
   * Dynamic configuration entities are applied according to override priority.
   *
   * @return int
   *   The priority of this entity.
   */
  public function getPriority() : int;

  /**
   * Evaluate whether the criteria is met to apply the overrides.
   *
   * @return bool
   *   TRUE if the conditions criteria are met to apply this dynamic config.
   */
  public function evaluate() : bool;

  /**
   * Check whether the dynamic configuration is enabled.
   *
   * @return bool
   *   TRUE if the configuration is enabled.
   */
  public function isEnabled() : bool;

  /**
   * Check whether the dynamic configuration is disabled.
   *
   * @return bool
   *   TRUE if the configuration is disabled.
   */
  public function isDisabled() : bool;

}
