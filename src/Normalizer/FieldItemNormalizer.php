<?php

namespace Drupal\tealiumiq\Normalizer;

use Drupal\serialization\Normalizer\NormalizerBase;

/**
 * Converts the tealiumiq field item object structure to tealium udo array.
 *
 * @see \Drupal\metatag\Normalizer\FieldItemNormalizer
 */
class FieldItemNormalizer extends NormalizerBase {

  /**
   * {@inheritdoc}
   */
  protected $supportedInterfaceOrClass = 'Drupal\tealiumiq\Plugin\Field\FieldType\TealiumiqFieldItem';

  /**
   * {@inheritdoc}
   */
  public function normalize($field_item, $format = NULL, array $context = []) {
    $values = $field_item->getValue();
    $normalized = unserialize($values['value']);
    return $normalized;
  }

}
