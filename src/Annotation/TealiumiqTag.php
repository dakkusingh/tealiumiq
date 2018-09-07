<?php

namespace Drupal\tealiumiq\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a TealiumiqTag annotation object.
 *
 * @Annotation
 */
class TealiumiqTag extends Plugin {

  /**
   * The Tealiumiq tag plugin's internal ID, in machine name format.
   *
   * @var string
   */
  public $id;

  /**
   * The display label/name of the tealiumiq tag plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * A longer explanation of what the field is for.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

  /**
   * Proper name of the actual Tealiumiq tag itself.
   *
   * @var string
   */
  public $name;

  /**
   * TealiumiqGroup.
   *
   * The group this tealiumiq tag fits in,
   * corresponds to a TealiumiqGroup plugin.
   *
   * @var string
   */
  public $group;

  /**
   * Weight of the tag.
   *
   * @var int
   */
  public $weight;

  /**
   * Type of the Tealiumiq tag.
   *
   * Should be either 'date', 'image', 'integer', 'label', 'string' or 'uri'.
   *
   * @var string
   */
  public $type;

  /**
   * True if URL must use HTTPS.
   *
   * @var bool
   */
  protected $secure;

  /**
   * True if more than one is allowed.
   *
   * @var bool
   */
  public $multiple;

  /**
   * True if the URL value(s) must be absolute.
   *
   * @var bool
   */
  protected $absoluteUrl;

}
