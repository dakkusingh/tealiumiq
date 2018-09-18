<?php

namespace Drupal\tealiumiq\EventSubscriber;

use Drupal\tealiumiq\Event\AlterUdoPropertiesEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * {@inheritdoc}
 */
class AlterUdoProperties implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[AlterUdoPropertiesEvent::UDO_ALTER_PROPERTIES] = 'doAlterUdoProperties';
    return $events;
  }

  /**
   * Alter the UDO.
   *
   * @param \Drupal\tealiumiq\Event\AlterUdoPropertiesEvent $event
   *   Alter Udo Properties Event.
   */
  public function doAlterUdoProperties(AlterUdoPropertiesEvent $event) {
    // Example.
    /*
    $properties = $event->getProperties();
    $properties['custom_var'] = '[current-page:title]';
    $event->setProperties($properties);
     */
  }

}
