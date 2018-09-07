<?php

namespace Drupal\tealiumiq\Service;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * A Plugin to manage your tealiumiq tag group.
 */
class GroupPluginManager extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces,
                              CacheBackendInterface $cache_backend,
                              ModuleHandlerInterface $module_handler) {
    $subdir = 'Plugin/tealium/Group';

    // The name of the annotation class that contains the plugin definition.
    $plugin_definition_annotation_name = 'Drupal\tealiumiq\Annotation\TealiumiqGroup';

    parent::__construct($subdir, $namespaces, $module_handler, NULL, $plugin_definition_annotation_name);

    $this->alterInfo('tealiumiq_groups');

    $this->setCacheBackend($cache_backend, 'tealiumiq_groups');
  }

}
