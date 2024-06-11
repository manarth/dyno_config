<?php

declare(strict_types=1);

namespace Drupal\dyno_config;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\dyno_config\Compiler\DynoConfigCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

/**
 * Dynamically generate a separate override for each conditional config.
 */
class DynoConfigServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    $container->addCompilerPass(new DynoConfigCompilerPass(), PassConfig::TYPE_OPTIMIZE);
  }

}
