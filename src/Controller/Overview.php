<?php

namespace Drupal\tealiumiq\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class Tealiumiq Overview.
 *
 * @package Drupal\tealiumiq\Controller
 */
class Overview extends ControllerBase {

  /**
   * Overview method.
   *
   * @return array
   *   Overview Markup.
   */
  public function doOverview() {
    // TODO This could be better.
    return [
      '#type' => 'inline_template',
      '#template' => $this->t("If you are new to Tealium and Tag Management this overview will prepare you to start working with iQ Tag Management. Before you begin, here is a quick video overview of tag management.") .
      '<br /><br /><iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/xEjc0XPK-7o" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>' .
      '<br /><br /><iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/djAm4M0ZPRs" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>' .
      '<br /><br />Learn more at <a href="https://community.tealiumiq.com/t5/iQ-Tag-Management/Tag-Management-Concepts/ta-p/15883" target="_blank">Tag Management Concepts</a>',
    ];
  }

}
