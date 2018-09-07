<?php

namespace Drupal\tealiumiq\Plugin\tealiumiq\Group;

use Drupal\tealiumiq\Annotation\TealiumiqGroup;

/**
 * The page group.
 *
 * @TealiumiqGroup(
 *   id = "page",
 *   label = @Translation("Page tags"),
 *   description = @Translation("Page Tealiumiq tags."),
 *   weight = 1
 * )
 */
class Page extends TealiumiqGroup {
  // Inherits everything from Base.
}
