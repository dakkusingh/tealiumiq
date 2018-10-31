<?php

namespace Drupal\tealiumiq;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\tealiumiq\Normalizer\FieldItemNormalizer;
use Drupal\tealiumiq\Normalizer\TealiumiqHalNormalizer;
use Drupal\tealiumiq\Normalizer\TealiumiqNormalizer;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Service Provider for Tealium.
 *
 * @see \Drupal\metatag\MetatagServiceProvider
 */
class TealiumiqServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $modules = $container->getParameter('container.modules');

    if (isset($modules['serialization'])) {
      // Serialization module is enabled, add our tealiumiq normalizers.
      // Priority of the tealiumiq normalizer must be higher than other
      // general-purpose typed data and field item normalizers.
      $tealium = new Definition(TealiumiqNormalizer::class);
      $tealium->addTag('normalizer', ['priority' => 30]);
      $container->setDefinition('tealiumiq.normalizer.tealium', $tealium);

      $tealium_hal = new Definition(TealiumiqHalNormalizer::class);
      $tealium_hal->addTag('normalizer', ['priority' => 31]);
      $container->setDefinition('tealiumiq.normalizer.tealium.hal', $tealium_hal);

      $tealium_field = new Definition(FieldItemNormalizer::class);
      $tealium_field->addTag('normalizer', ['priority' => 30]);
      $container->setDefinition('tealiumiq.normalizer.tealium_field', $tealium_field);
    }
  }

}
