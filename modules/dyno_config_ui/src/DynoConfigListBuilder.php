<?php

declare(strict_types=1);

namespace Drupal\dyno_config_ui;

use Drupal\Core\Config\Entity\DraggableListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\dyno_config\Entity\DynoConfigInterface;

/**
 * List-builder handler for the dynamic configuration entities.
 */
class DynoConfigListBuilder extends DraggableListBuilder {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dyno_config.weight';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $form['#attached']['library'][] = 'dyno_config_ui/admin';

    $form[$this->entitiesKey]['#attributes']['class'][] = 'dyno-config';
    $form[$this->entitiesKey]['#attributes']['class'][] = 'list-builder';

    $form['description'] = [
      '#type'   => 'markup',
      '#markup' => '<p>' . $this->t('Configuration overrides are processed in order from top to bottom.<br />Those at the bottom of the list have the highest priority.') . '</p>',
      '#weight' => 10,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [
      'id'          => $this->t('ID'),
      'status'      => $this->t('Enabled'),
      'label'       => $this->t('Label'),
      'description' => $this->t('Description'),
    ];

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\dyno_config\Entity\DynoConfigInterface $entity */
    $row = [
      '#attributes' => [
        'class' => [
        ],
      ],
    ];
    if ($entity->isDisabled()) {
      $row['#attributes']['class'][] = 'config-disabled';
    }
    // $row['#attributes']['class'][] = ($entity->evaluate())
    //   ? 'status-active'
    //   : 'status-inactive';

    $row[] = [
      '#type' => 'link',
      '#title' => $entity->id(),
      '#url' => $entity->toUrl('edit-form'),
    ];
    $row[] = [
      '#type' => 'markup',
      '#markup' => $entity->status() == DynoConfigInterface::ENABLED ? 'Y' : 'N',
    ];

    $row[] = [
      '#type' => 'markup',
      '#markup' => $entity->label() ?? '',
    ];
    $row[] = [
      '#type' => 'markup',
      '#markup' => $entity->get('description') ?? '',
    ];

    $row = $row + parent::buildRow($entity);
    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);

    if ($entity->access('update')) {
      if ($entity->hasLinkTemplate('edit-conditions-form')) {
        $operations['edit-conditions'] = [
          'title'  => $this->t('Edit criteria'),
          'url'    => $this->ensureDestination($entity->toUrl('edit-conditions-form')),
          'weight' => 20,
        ];
      }

      if ($entity->hasLinkTemplate('edit-config-overrides-form')) {
        $operations['edit-overrides'] = [
          'title'  => $this->t('Edit overrides'),
          'url'    => $this->ensureDestination($entity->toUrl('edit-config-overrides-form')),
          'weight' => 25,
        ];
      }
    }

    return $operations;
  }

}
