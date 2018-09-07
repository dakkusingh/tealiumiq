<?php

namespace Drupal\tealiumiq\Plugin\tealiumiq\Tag;

use Drupal\tealiumiq\TealiumiqTagBase;

/**
 * The basic "Page Name" meta tag.
 *
 * @TealiumiqTag(
 *   id = "page_name",
 *   label = @Translation("Page Name"),
 *   description = @Translation("Page Name."),
 *   name = "page_name",
 *   group = "page",
 *   weight = 4,
 *   type = "label",
 *   secure = FALSE,
 *   multiple = FALSE
 * )
 */
class PageName extends TealiumiqTagBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}
