<?php

declare(strict_types=1);

namespace Drupal\dyno_config_ui\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\dyno_config\Entity\DynoConfigInterface;

/**
 * Base class for Entity Forms for a dynamic configuration entity.
 */
abstract class DynoConfigEntityForm extends EntityForm {

  /**
   * The entity being used by this form.
   *
   * @var \Drupal\dyno_config\Entity\DynoConfigInterface
   */
  protected $entity;

  /**
   * {@inheritdoc}
   */
  public function getEntity() : DynoConfigInterface {
    return $this->entity;
  }

}
