<?php

namespace Drupal\tealiumiq\Normalizer;

use Drupal\serialization\Normalizer\NormalizerBase;

/**
 * Normalizes Tealium into the viewed entity.
 *
 * @see \Drupal\metatag\Normalizer\MetatagNormalizer
 */
class TealiumiqNormalizer extends NormalizerBase {

  /**
   * {@inheritdoc}
   */
  protected $supportedInterfaceOrClass = 'Drupal\tealiumiq\Plugin\Field\TealiumiqEntityFieldItemList';

  /**
   * {@inheritdoc}
   */
  public function normalize($field_item, $format = NULL, array $context = []) {
    $entity = $field_item->getEntity();

    // Do the Tealium thing.
    // TODO DI.
    $tealiumiq = \Drupal::service('tealiumiq.tealiumiq');
    $tealiumiq->setUdoPropertiesFromEntity($entity);
    $tags = $tealiumiq->getProperties();

    $normalized = [];
    if (!empty($tags)) {
      $normalized = $tags;
    }

    return $normalized;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsDenormalization($data, $type, $format = NULL) {
    return FALSE;
  }

}
