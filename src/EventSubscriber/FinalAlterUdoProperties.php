<?php

namespace Drupal\tealiumiq\EventSubscriber;

use Drupal\tealiumiq\Event\FinalAlterUdoPropertiesEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * {@inheritdoc}
 */
class FinalAlterUdoProperties implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[FinalAlterUdoPropertiesEvent::FINAL_UDO_ALTER_PROPERTIES] = 'doFinalUdoProperties';
    return $events;
  }

  /**
   * Final Alter the UDO.
   *
   * @param \Drupal\tealiumiq\Event\FinalAlterUdoPropertiesEvent $event
   *   Final Alter Udo Properties Event.
   */
  public function doFinalUdoProperties(FinalAlterUdoPropertiesEvent $event) {
    // Example.
    /*
    $properties = $event->getProperties();
    $properties['pageName'] = $properties['page_name'];
    unset($properties['page_name']);
    $properties['pageUrl'] = $properties['page_url'];
    unset($properties['page_url']);
    $event->setProperties($properties);
     */
  }

}
