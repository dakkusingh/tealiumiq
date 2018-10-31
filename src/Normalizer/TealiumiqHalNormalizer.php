<?php

namespace Drupal\tealiumiq\Normalizer;

/**
 * Converts the Tealiumiq field item object structure to Tealiumiq UDO array.
 *
 * @see \Drupal\metatag\Normalizer\MetatagHalNormalizer
 */
class TealiumiqHalNormalizer extends TealiumiqNormalizer {

  /**
   * {@inheritdoc}
   */
  protected $format = ['hal_json'];

  /**
   * {@inheritdoc}
   */
  public function normalize($field_item, $format = NULL, array $context = []) {
    $normalized = parent::normalize($field_item, $format, $context);

    // Mock the field array similar to the other fields.
    // @see Drupal\hal\Normalizer\FieldItemNormalizer
    return [
      'tealium' => [$normalized],
    ];
  }

}
