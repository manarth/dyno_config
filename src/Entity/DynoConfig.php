<?php

declare(strict_types=1);

namespace Drupal\dyno_config\Entity;

use Drupal\Core\Condition\ConditionInterface;
use Drupal\Core\Condition\ConditionManager;
use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * A Dynamic Configuration entity defines dynamic run-time configuration.
 *
 * @ConfigEntityType(
 *   id = "dyno_config",
 *   config_prefix = "entity",
 *   label = @Translation("Dynamic Configuration"),
 *   translatable = FALSE,
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "status" = "status",
 *     "uuid" = "uuid",
 *     "weight" = "priority"
 *   },
 *   config_export = {
 *     "id",
 *     "uuid",
 *     "label",
 *     "status",
 *     "description",
 *     "conditions",
 *     "priority",
 *   }
 * )
 */
class DynoConfig extends ConfigEntityBase implements DynoConfigInterface {

  /**
   * Collection of conditions which must be met to trigger the dynamic config.
   *
   * @var array
   */
  protected $conditions = [];

  /**
   * Order in which dynamic configuration is evaluated.
   *
   * @var int
   */
  protected int $priority = 0;

  /**
   * The manager for plugin conditions.
   *
   * @var \Drupal\Core\Condition\ConditionManager
   */
  protected ConditionManager $conditionManager;

  /**
   * Set the condition plugin manager.
   *
   * @param \Drupal\Core\Condition\ConditionManager $manager
   *   The manager for plugin conditions.
   */
  public function setConditionPluginManager(ConditionManager $manager) : void {
    $this->conditionManager = $manager;
  }

  /**
   * Get the condition plugin manager.
   *
   * @return \Drupal\Core\Condition\ConditionManager
   *   The manager for plugin conditions.
   */
  public function getConditionPluginManager() : ConditionManager {
    if (empty($this->conditionManager)) {
      $this->conditionManager = \Drupal::service('plugin.manager.condition');
    }
    return $this->conditionManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getCollectionName() : string {
    return self::COLLECTION_NAME_PREFIX . $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() : bool {
    if (empty($this->conditions)) {
      error_log('Loading conditions');
      $this->loadConditions();
    }

    $result = TRUE;
    foreach ($this->conditions as $condition) {
      error_log('Evaluating a condition.');
      $status = self::execute($condition);

      error_log('Status is: ' . (int) $status);
      $result = $result && $status;
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getPriority() : int {
    return $this->priority;
  }

  /**
   * {@inheritdoc}
   */
  public function isEnabled() : bool {
    return $this->status == self::ENABLED;
  }

  /**
   * {@inheritdoc}
   */
  public function isDisabled() : bool {
    return $this->status == self::DISABLED;
  }

  protected function loadConditions() : void {
    if (count($this->conditions)) {
      return;
    }

    /** @var \Drupal\Core\Condition\ConditionManager */
    $manager = \Drupal::service('plugin.manager.condition');

    $configuration = [
      'pages' => implode("\n", [
        '<front>',
        '/admin',
        '/admin/*'
      ]),
    ];

    /** @var \Drupal\system\Plugin\Condition\RequestPath */
    $plugin = $manager->createInstance('request_path', $configuration);
    $this->conditions[] = $plugin;
  }

  /**
   * {@inheritdoc}
   */
  protected static function execute(ConditionInterface $condition) : bool {
    $result = $condition->evaluate();
    return $condition->isNegated() ? !$result : $result;
  }

}
