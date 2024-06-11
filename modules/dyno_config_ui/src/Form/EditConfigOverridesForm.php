<?php

declare(strict_types=1);

namespace Drupal\dyno_config_ui\Form;

use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\dyno_config\Entity\DynoConfigInterface;
use Drupal\dyno_config\Services\DynoConfigManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Edit form to configure the configuration overrides for dynamic config.
 */
class EditConfigOverridesForm extends DynoConfigEntityForm {

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
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dyno_config.manager')
    );
  }

  /**
   * Title callback.
   *
   * @param \Drupal\dyno_config\Entity\DynoConfigInterface $dyno_config
   *   The dynamic configuration entity.
   *
   * @return array
   *   Renderable array for the page title.
   */
  public function getTitle(DynoConfigInterface $dyno_config) {
    return $this->t('Edit overrides for %label',[
      '%label' => $dyno_config->label()
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['#attached']['library'][] = 'dyno_config_ui/admin';

    $form['table'] = [
      '#type' => 'tableselect',
      '#header' => $this->getHeader(),
      '#options' => $this->getRows(),
      '#empty' => $this->t('No overrides are set.'),
    ];

    return $form;
  }

  /**
   * Get the header for the table which lists the overrides.
   *
   * @return array
   *   Array of table header names.
   */
  protected function getHeader() : array {
    return [
      $this->t('Config name'),
      $this->t('Key'),
      $this->t('Value'),
      $this->t('Operations'),
    ];
  }

  /**
   * Get the rows for the table which lists the overrides.
   *
   * @return array
   *   Array of override definitions.
   */
  protected function getRows() : array {
    $rows = [];
    foreach ($this->getStorage()->listAll() as $config_name) {
      $config = $this->flatten($this->getStorage()->read($config_name));
      foreach ($config as $key => $value) {
        $row = [];
        $row[] = $config_name;
        $row[] = $key;
        $row[] = $value;
        $row[] = '';

        $rows[] = $row;
      }
    }
    return $rows;
  }

  /**
   * Get the config storage for this dynamic configuration entity.
   *
   * @return \Drupal\Core\Config\StorageInterface
   *   The config collection.
   */
  protected function getStorage() : StorageInterface {
    return $this->manager->getCollectionFor($this->entity->id());
  }

  /**
   * Flatten nested arrays into the top level as dotted keys.
   *
   * Original keys remain intact.
   *
   * @param array  $config
   * @param string $prefix
   *
   * @return array
   */
  private static function flatten(array $config, $prefix = '') {
    $flat = [];
    foreach ($config as $name => $value) {
      $flat[$prefix . $name] = $value;
      if (is_array($value)) {
        $flat += self::flatten($value, $prefix . $name . '.');
      }
    }
    return $flat;
  }

}
