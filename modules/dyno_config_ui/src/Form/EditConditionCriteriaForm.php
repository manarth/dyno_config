<?php

declare(strict_types=1);

namespace Drupal\dyno_config_ui\Form;

use Drupal\Core\Condition\ConditionManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\dyno_config\Entity\DynoConfigInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Edit form to configure the condition criteria for dynamic config.
 */
class EditConditionCriteriaForm extends DynoConfigEntityForm {

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
    return $this->t('Edit condition criteria for %label',[
      '%label' => $dyno_config->label()
    ]);
  }

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Condition\ConditionManager $conditionManager
   *   The manager for condition plugins.
   * @param \Drupal\Core\Plugin\Context\ContextRepositoryInterface $contextRepository
   *   The context repository service.
   */
  public function __construct(
    protected ConditionManager $conditionManager,
    protected ContextRepositoryInterface $contextRepository) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.condition'),
      $container->get('context.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    // Condition plugins don't use context repositories directly; they read the
    // available contexts from the form state.
    $form_state->setTemporaryValue('gathered_contexts',
      $this->contextRepository->getAvailableContexts());
    $form['#attached']['library'][] = 'dyno_config_ui/admin';

    return $form;
  }

}
