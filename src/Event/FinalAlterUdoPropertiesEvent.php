<?php

namespace Drupal\tealiumiq\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class SetUdoProperties.
 *
 * @package Drupal\tealiumiq\Event
 */
class FinalAlterUdoPropertiesEvent extends Event {

  const FINAL_UDO_ALTER_PROPERTIES = 'final.tealiumiq.udo.properties';

  protected $properties;
  protected $namespace;

  /**
   * Constructor.
   *
   * @param string $namespace
   *   UDO Namespace.
   * @param array $properties
   *   UDO Properties array.
   */
  public function __construct($namespace, array $properties) {
    $this->namespace = $namespace;
    $this->properties = $properties;
  }

  /**
   * Getter UDO Properties array.
   *
   * @return array
   *   UDO Properties array.
   */
  public function getProperties() {
    return $this->properties;
  }

  /**
   * Setter UDO Properties array.
   *
   * @param array $properties
   *   UDO Properties array.
   */
  public function setProperties(array $properties) {
    $this->properties = $properties;
  }

  /**
   * Getter UDO Namespace.
   *
   * @return string
   *   UDO Namespace
   */
  public function getNamespace() {
    return $this->namespace;
  }

}
