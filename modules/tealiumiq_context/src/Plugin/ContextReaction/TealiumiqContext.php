<?php

namespace Drupal\tealiumiq_context\Plugin\ContextReaction;

use Drupal\context\ContextReactionPluginBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a content reaction that adds a Tealium iQ Tags.
 *
 * @ContextReaction(
 *   id = "tealiumiq_context",
 *   label = @Translation("Tealium iQ Tags")
 * )
 */
class TealiumiqContext extends ContextReactionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // TODO DI tealium iq service.
    $tealiumiq = \Drupal::service('tealiumiq.tealiumiq');
    $tags = $tealiumiq->helper->sortedTags();
    $values = [];

    foreach ($tags as $tag_id => $tag_definition) {
      if (isset($this->getConfiguration()[$tag_id])) {
        $values[$tag_id] = $this->getConfiguration()[$tag_id];
      }
    }

    $form = $tealiumiq->form($values, []);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    // TODO DI tealium iq service.
    $tealiumiq = \Drupal::service('tealiumiq.tealiumiq');
    $tags = $tealiumiq->helper->sortedTags();
    $conf = [];

    foreach ($tags as $tag_id => $tag_definition) {
      if ($form_state->hasValue([$tag_definition['group'], $tag_id])) {
        $conf[$tag_id] = $form_state->getValue([$tag_definition['group'], $tag_id]);
      }
    }

    $this->setConfiguration($conf);
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->getConfiguration()['tealiumiq_context'];
  }

  /**
   * {@inheritdoc}
   */
  public function execute(array &$vars = []) {
    $config = $this->getConfiguration();
    return $config;
  }

}
