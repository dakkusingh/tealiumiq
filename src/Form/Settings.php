<?php

namespace Drupal\tealiumiq\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Tealium iQ Settings.
 */
class Settings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tealiumiq_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['tealiumiq.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $settings = $this->config('tealiumiq.settings');

    $form['account'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Account'),
      '#default_value' => $settings->get('account'),
      '#size' => 20,
      '#required' => TRUE,
    ];

    $form['profile'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Profile'),
      '#default_value' => $settings->get('profile'),
      '#size' => 20,
      '#required' => TRUE,
    ];

    $form['environment'] = [
      '#type' => 'radios',
      '#title' => $this->t('Environment'),
      '#description' => $this->t('Choose the environment.'),
      '#options' => [
        'dev' => $this->t('Development'),
        'qa' => $this->t('Testing / QA'),
        'prod' => $this->t('Production'),
      ],
      '#default_value' => $settings->get('environment'),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('tealiumiq.settings')
      ->set('account', $form_state->getValue('account'))
      ->set('profile', $form_state->getValue('profile'))
      ->set('environment', $form_state->getValue('environment'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
