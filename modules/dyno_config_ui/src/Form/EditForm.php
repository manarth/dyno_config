<?php

declare(strict_types=1);

namespace Drupal\dyno_config_ui\Form;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\dyno_config\Entity\DynoConfigInterface;

/**
 * Edit form for a dynamic configuration entity.
 */
class EditForm extends DynoConfigEntityForm {

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
    return $this->t('Edit %label',[
      '%label' => $dyno_config->label()
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['#attached']['library'][] = 'dyno_config_ui/admin';

    /** @var \Drupal\dyno_config\Entity\DynoConfigInterface */
    $entity = $this->entity;

    $form['label'] = [
      '#title' => $this->t('Label'),
      '#type' => 'textfield',
      '#default_value' => $entity->label(),
      '#required' => TRUE,
      '#size' => 30,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $entity->id(),
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#machine_name' => [
        'exists' => ['Drupal\dyno_config\Entity\DynoConfig', 'load'],
      ],
      '#disabled' => !$this->entity->isNew(),
      '#description' => $this->t('A unique machine-readable name for this entity. It must only contain lowercase letters, numbers, and underscores.'),
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#default_value' => $entity->get('description'),
    ];

    $form['status'] = [
      '#title' => $this->t('Enabled'),
      '#type' => 'checkbox',
      '#default_value' => $entity->status(),
    ];

    $form['priority'] = [
      '#type' => 'weight',
      '#title' => $this->t('Priority'),
      '#default_value' => $entity->get('priority') ?? 0,
      '#delta' => 255,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $form_state->setRedirect('entity.dyno_config.collection');
    return parent::save($form, $form_state);
  }

}
