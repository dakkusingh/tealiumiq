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

    $form['tag_load'] = [
      '#type' => 'radios',
      '#title' => $this->t('Tag loading'),
      '#description' => $this->t('Load the tag Asynchronously or Synchronously.'),
      '#default_value' => $settings->get('tag_load'),
      '#options' => [
        'async' => $this->t('Asynchronous'),
        'sync' => $this->t('Synchronous'),
      ],
      '#required' => TRUE,
    ];

    $form['sync_load_position'] = [
      '#type' => 'radios',
      '#title' => $this->t('Synchronous tag loading position'),
      '#description' => $this->t('Add Tealium iQ Tags to the top or bottom of the page.'),
      '#default_value' => $settings->get('sync_load_position'),
      '#options' => [
        'top' => $this->t('Top of the page.'),
        'bottom' => $this->t('Bottom of the page.'),
      ],
      '#states' => [
        'visible' => [
          ':input[name="tag_load"]' => ['value' => 'sync'],
        ],
      ],
      '#required' => TRUE,
    ];

    $form['api_only'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('API Only'),
      '#description' => $this->t('Check this option for decoupled sites or custom tag implementations. 
                                  When checked, the core module will not output tags to the page - 
                                  allowing you to have a custom implementation in your own modules.'),
      '#default_value' => $settings->get('api_only'),
    ];

    $form['defaults_everywhere'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Defaults Everywhere'),
      '#description' => $this->t('Use default tags everywhere as the base, 
                                  these can then be overridden by entity, context and custom modules. 
                                  For advanced use cases, you might want to disable defaults.'),
      '#default_value' => $settings->get('defaults_everywhere'),
    ];

    $form['defer_fields'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Defer field tags'),
      '#description' => $this->t('This changes the order in which the tags are applied. 
                                  if checked, tags will be applied as 
                                  1) Default tags 
                                  2) Context + Custom Events 
                                  3) Field tags from entities.'),
      '#default_value' => $settings->get('defer_fields'),
    ];

    $form['json_encoded'] = [
      '#type' => 'radios',
      '#title' => $this->t('JSON Encoded'),
      '#description' => $this->t('Using Drupal: use Json::encode,
                                  Using PHP: use json_encode.'),
      '#options' => [
        'dru' => $this->t('Using Drupal'),
        'php' => $this->t('Using PHP'),
      ],
      '#default_value' => $settings->get('json_encoded'),
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
      ->set('tag_load', $form_state->getValue('tag_load'))
      ->set('sync_load_position', $form_state->getValue('sync_load_position'))
      ->set('api_only', $form_state->getValue('api_only'))
      ->set('defaults_everywhere', $form_state->getValue('defaults_everywhere'))
      ->set('defer_fields', $form_state->getValue('defer_fields'))
      ->set('json_encoded', $form_state->getValue('json_encoded'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
