<?php

namespace Drupal\tealiumiq_context\EventSubscriber;

use Drupal\context\ContextManager;
use Drupal\tealiumiq\Event\AlterUdoPropertiesEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * {@inheritdoc}
 */
class AlterUdoProperties implements EventSubscriberInterface {

  /**
   * ContextManager.
   *
   * @var \Drupal\context\ContextManager
   */
  private $contextManager;

  /**
   * ContextManager constructor.
   */
  public function __construct(ContextManager $contextManager) {
    $this->contextManager = $contextManager;
  }

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
    foreach ($this->contextManager->getActiveReactions('tealiumiq_context') as $reaction) {
      $tealiumiqContext = array_filter($reaction->execute());

      // Unset the ID.
      unset($tealiumiqContext['id']);

      if (!empty($tealiumiqContext)) {
        // Get the current properties. Maybe other events set these!
        $properties = $event->getProperties();

        // Be inclusive, do not replace all properties.
        $properties = array_merge($properties, $tealiumiqContext);

        // Set them.
        $event->setProperties($properties);
      }
    }
  }

}
