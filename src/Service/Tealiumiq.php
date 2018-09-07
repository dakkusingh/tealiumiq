<?php

namespace Drupal\tealiumiq\Service;

use Drupal\Core\Config\ConfigFactory;

/**
 * Class Tealiumiq.
 *
 * @package Drupal\tealiumiq\Service
 */
class Tealiumiq {

  /**
   * Config.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * UDO.
   *
   * @var \Drupal\tealiumiq\Service\Udo
   */
  protected $udo;

  /**
   * Tealiumiq constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config
   *   Config Factory.
   * @param \Drupal\tealiumiq\Service\Udo $udo
   *   UDO Service.
   */
  public function __construct(ConfigFactory $config,
                              Udo $udo) {
    // Get Tealium iQ Settings.
    $this->config = $config->get('tealiumiq.settings');

    // Tealium iQ Settings.
    $this->account = $this->config->get('account');
    $this->profile = $this->config->get('profile');
    $this->environment = $this->config->get('environment');
    $this->async = $this->config->get('async');

    // UDO.
    $this->udo = $udo;

  }

  /**
   * Get Account Value.
   *
   * @return string
   *   Account value.
   */
  public function getAccount() {
    return $this->account;
  }

  /**
   * Get profile Value.
   *
   * @return string
   *   profile value.
   */
  public function getProfile() {
    return $this->profile;
  }

  /**
   * Get environment Value.
   *
   * @return string
   *   environment value.
   */
  public function getEnvironment() {
    return $this->environment;
  }

  /**
   * Get async Value.
   *
   * @return string
   *   async value.
   */
  public function getAsync() {
    return $this->async;
  }

}
