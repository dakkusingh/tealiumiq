<?php

namespace Drupal\tealiumiq\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\tealiumiq\Service\Tealiumiq;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Tealium iQ Defaults.
 */
class Defaults extends ConfigFormBase {

  /**
   * Tealiumiq Service.
   *
   * @var \Drupal\tealiumiq\Service\Tealiumiq
   */
  private $tealiumiq;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   ConfigFactoryInterface.
   * @param \Drupal\tealiumiq\Service\Tealiumiq $tealiumiq
   *   Tealiumiq Service.
   */
  public function __construct(ConfigFactoryInterface $configFactory,
                              Tealiumiq $tealiumiq) {
    parent::__construct($configFactory);
    $this->tealiumiq = $tealiumiq;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('tealiumiq.tealiumiq')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tealiumiq_defaults';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['tealiumiq.defaults'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $defaults = $this->config('tealiumiq.defaults');

    $values = [];
    if (!empty($defaults->get())) {
      $values = $defaults->get();
    }

    $form = $this->tealiumiq->form($values, []);

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $tags = $this->tealiumiq->helper->sortedTags();

    foreach ($tags as $tag_id => $tag_definition) {
      if ($form_state->hasValue($tag_id)) {
        $this->config('tealiumiq.defaults')
          ->set($tag_id, $form_state->getValue($tag_id))
          ->save();
      }
    }

    parent::submitForm($form, $form_state);
  }

}
