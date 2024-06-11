<?php

declare(strict_types=1);

namespace Drupal\dyno_config_ui\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\dyno_config\Entity\DynoConfigInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Form to disable or enable a dynamic configuration entity.
 */
class ToggleStatusForm extends EntityConfirmFormBase {

  use \Drupal\Core\Messenger\MessengerTrait;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $action = NULL) {
    // The form is used for both `enable` and `disable` options.
    // If the `enable` link is used for an enabled entity, redirect back to the
    // cancel URL with a message (and vice-versa for `disable`).
    if ($action == 'enable' && $this->getEntity()->status()) {
      $this->messenger()->addMessage('Configuration is already enabled.');
      return new RedirectResponse($this->getCancelUrl()->toString());
    }
    if ($action == 'disable' && !$this->getEntity()->status()) {
      $this->messenger()->addMessage('Configuration is already disabled.');
      return new RedirectResponse($this->getCancelUrl()->toString());
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->getEntity()->status()
      ? $this->t('Are you sure you want to disable this configuration?')
      : $this->t('Are you sure you want to enable this configuration?');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->getEntity()->get('description');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.dyno_config.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->getEntity()->status()
      ? $this->t('Disable')
      : $this->t('Enable');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->getEntity()
      ->setStatus(!$this->getEntity()->status())
      ->save();

    $message = $this->getEntity()->status()
      ? $this->t('Configuration is enabled.')
      : $this->t('Configuration is disabled.');
    $this->messenger()->addMessage($message);

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

  /**
   * {@inheritdoc}
   */
  public function getEntity() : DynoConfigInterface {
    return $this->entity;
  }

}
