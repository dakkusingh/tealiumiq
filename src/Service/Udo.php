<?php

namespace Drupal\tealiumiq\Service;

use Drupal\Component\Serialization\Json;

/**
 * Class Udo.
 *
 * @package Drupal\tealiumiq\Service
 */
class Udo {

  /**
   * Namespace.
   *
   * @var string
   */
  protected $namespace;

  /**
   * Properties.
   *
   * @var array
   */
  public $properties;

  /**
   * UDO Constructor.
   *
   * @param string $namespace
   *   The JavaScript namespace to use.
   * @param array $properties
   *   The default properties.
   */
  public function __construct($namespace = 'utag_data',
                              array $properties = []) {
    $this->namespace = $namespace;
    $this->properties = $properties;
  }

  /**
   * Export the UDO as JSON.
   *
   * @return string
   *   Json encoded output.
   */
  public function __toString() {
    return Json::encode($this->properties);
  }

  /**
   * Get the UDO namespace.
   *
   * @return string
   *   Namespace value.
   */
  public function getNamespace() {
    return $this->namespace;
  }

  /**
   * Set the UDO namespace.
   *
   * @param string $namespace
   *   UDO namespace.
   */
  public function setNamespace($namespace) {
    $this->namespace = $namespace;
  }

  /**
   * Gets all data values.
   *
   * @return array
   *   All variables.
   */
  public function getProperties() {
    return $this->properties;
  }

  /**
   * Sets all data values.
   *
   * @return $this
   */
  public function setProperties($properties) {
    if (is_array($properties)) {
      $this->properties = $properties;
    }

    return $this;
  }

  /**
   * Gets single data value.
   *
   * @return string
   *   single data value.
   */
  public function getProperty($name) {
    $value = NULL;

    if (array_key_exists($name, $this->properties)) {
      $value = $this->properties[$name];
    }

    return $value;
  }

  /**
   * Sets single data value.
   *
   * @return $this
   */
  public function setProperty($name, $value) {
    $this->properties[$name] = $value;

    return $this;
  }

}
