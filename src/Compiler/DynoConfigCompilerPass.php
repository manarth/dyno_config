<?php

declare(strict_types=1);

namespace Drupal\dyno_config\Compiler;

use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Register each dynamic configuration entity as a config factory override.
 */
class DynoConfigCompilerPass implements CompilerPassInterface {

  /**
   * The service ID for every entity is this prefix followed by the entity ID.
   */
  const SERVICE_KEY_PREFIX = 'dyno_config.instance.';

  /**
   * Prefix used when generating the config name for an entity.
   */
  const CONFIG_NAME_PREFIX = 'dyno_config.entity.';

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container) {
    if (!self::requirementsAreMet($container)) {
      return;
    }

    /** @var \Symfony\Component\DependencyInjection\Definition */
    $configFactoryDefinition = $container->getDefinition('config.factory');

    /** @var \Symfony\Component\DependencyInjection\Definition */
    $abstractDefinition = $container->getDefinition('dyno_config.config_override.abstract');

    $entityIds = self::getEntities($container->get('config.factory'));
    foreach ($entityIds as $entityId => $priority) {
      $serviceKey = self::SERVICE_KEY_PREFIX . $entityId;
      if ($container->hasDefinition($serviceKey)) {
        continue;
      }

      $entityDefinition = clone($container->getDefinition('dyno_config.provider.entity'));
      $entityDefinition->addArgument($entityId);

      $storageDefinition = clone($container->getDefinition('dyno_config.provider.storage'));
      $storageDefinition->addArgument($entityId);

      // Service definition for the config-override service.
      $instanceDefinition = clone($abstractDefinition)
        ->addArgument($entityDefinition)
        ->addArgument($storageDefinition)
        ->addTag('config.factory.override', [
          'priority' => $priority,
        ])
        ->setAbstract(FALSE)
        ->setProperty('_serviceId', $serviceKey);
      $container->setDefinition($serviceKey, $instanceDefinition);

      // Manually attach to the config factory, as the TaggedServicesPass has
      // already completed and won't pick up these additional dynamic services.
      $configFactoryDefinition->addMethodCall('addOverride', [
        $container->getDefinition($serviceKey),
      ]);
    }
    // @todo Use $configFactoryDefinition->setMethodCalls() to reorder by Priority.
  }

  /**
   * Check that the required services are present.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
   *   The container builder.
   *
   * @return bool
   *   TRUE if the requirements are met.
   */
  protected static function requirementsAreMet(ContainerBuilder $container) : bool {
    $requiredServiceDefinitions = [
      'config.factory',
      'dyno_config.config_override.abstract',
      'dyno_config.provider.entity',
      'dyno_config.provider.storage',
    ];
    foreach ($requiredServiceDefinitions as $service) {
      if (!$container->hasDefinition($service)) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Get the entity IDs of the dynamic configuration entities.
   *
   * The config factory is used because entity-storage is not available.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $factory
   *   The config factory.
   *
   * @return array
   *   Associative array of entity IDs and their priority.
   */
  protected static function getEntities(ConfigFactoryInterface $factory) : array {
    $config = $factory->loadMultiple($factory->listAll(self::CONFIG_NAME_PREFIX));
    $priorityMap = array_map(fn ($config) => $config->get('priority') ?? 0, $config);

    return array_combine(
      array_map(fn ($key) => substr($key, strlen(self::CONFIG_NAME_PREFIX)), array_keys($priorityMap)),
      array_values($priorityMap),
    );
  }

}
