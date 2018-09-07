<?php

namespace Drupal\tealiumiq\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'tealiumiq_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "tealiumiq_formatter",
 *   module = "tealiumiq",
 *   label = @Translation("Empty formatter"),
 *   field_types = {
 *     "tealiumiq"
 *   }
 * )
 */
class TealiumiqFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    // Does not actually output anything.
    return [];
  }

}
