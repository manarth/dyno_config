<?php

declare(strict_types=1);

namespace Drupal\dyno_config\Drush\Commands;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\StorageInterface;
use Drupal\dyno_config\Services\DynoConfigManagerInterface;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Psr\Container\ContainerInterface as DrushContainer;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Config management commands for dynamic config entities.
 */
class DynoConfigDrushCommands extends DrushCommands {

  /**
   * Constructor.
   *
   * @param \Drupal\dyno_config\Services\DynoConfigManagerInterface $manager
   *   The manager service for dynamic configuration.
   */
  public function __construct(protected DynoConfigManagerInterface $manager) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, DrushContainer $drush): self {
    return new static($container->get('dyno_config.manager'));
  }

  /**
   * List the configuration names available in a dynamic configuration entity.
   */
  #[CLI\Command(name: 'dyno:config:list', aliases: ['dclist'])]
  #[CLI\Argument(name: 'entityId', description: 'The entity ID of a dynamic configuration entity.')]
  public function collectionList($entityId) {
    $config = $this->getStorage($entityId);
    return $config->listAll();
  }

  /**
   * Display a config value, or a whole configuration object.
   */
  #[CLI\Command(name: 'dyno:config:get', aliases: ['dcget'])]
  #[CLI\Argument(name: 'entityId', description: 'The entity ID of a dynamic configuration entity.')]
  #[CLI\Argument(name: 'config_name', description: 'The config object name, for example <info>system.site</info>.')]
  #[CLI\Argument(name: 'key', description: 'The config key, for example <info>page.front</info>. Optional.')]
  public function get($entityId, $config_name, $key = '', $options = ['format' => 'yaml']) {
    $config = $this->getStorage($entityId);
    $value = $config->read($config_name);
    return $key ? ["$config_name:$key" => $value] : $value;
  }

  /**
   * Save a config value directly.
   */
  #[CLI\Command(name: 'dyno:config:set', aliases: ['dcset'])]
  #[CLI\Argument(name: 'entityId', description: 'The entity ID of a dynamic configuration entity.')]
  #[CLI\Argument(name: 'config_name', description: 'The config object name, for example <info>system.site</info>.')]
  #[CLI\Argument(name: 'key', description: 'The config key, for example <info>page.front</info>. Use <info>?</info> if you are updating multiple top-level keys.')]
  #[CLI\Argument(name: 'value', description: 'The value to assign to the config key. Use <info>-</info> to read from Stdin.')]
  public function set($entityId, $config_name, $key, $value) {
    $config = $this->getStorage($entityId);
    $value = $config->read($config_name);
    NestedArray::setValue($value, explode('.', $key), $value);
    $config->write($config_name, $value);
  }

  /**
   * Delete a configuration key, or a whole object(s).
   */
  #[CLI\Command(name: 'dyno:config:delete', aliases: ['dcdel'])]
  #[CLI\Argument(name: 'entityId', description: 'The entity ID of a dynamic configuration entity.')]
  #[CLI\Argument(name: 'config_name', description: 'The config object name(s).')]
  #[CLI\Argument(name: 'key', description: 'A config key to clear, May not be used with multiple config names.')]
  #[CLI\Usage(name: 'drush config:delete system.site', description: 'Delete the system.site config object.')]
  #[CLI\Usage(name: 'drush config:delete system.site page.front', description: "Delete the 'page.front' key from the system.site object.")]
  public function delete($entityId, $config_name, $key = NULL) : void {
    $config = $this->getStorage($entityId);
    if ($key) {
      $value = $config->read($config_name);
      NestedArray::unsetValue($value, explode('.', $key));
      $config->write($config_name, $value);
    }
    else {
      $config->delete($config_name);
    }
  }

  /**
   * Get the config collection for an entity.
   *
   * @param string $entityId
   *   The machine-name of the dynamic configuration entity.
   *
   * @return \Drupal\Core\Config\StorageInterface
   *   The config collection used by that entity.
   */
  protected function getStorage($entityId) : StorageInterface {
    return $this->manager->getCollectionFor($entityId);
  }

}
