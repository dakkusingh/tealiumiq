<?php

namespace Drupal\tealiumiq\Plugin\Field\FieldWidget;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\tealiumiq\Service\TagPluginManager;
use Drupal\tealiumiq\Service\Tealiumiq;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Advanced widget for tealium field.
 *
 * @FieldWidget(
 *   id = "tealiumiq_widget",
 *   label = @Translation("Advanced tealium tags form"),
 *   field_types = {
 *     "tealiumiq"
 *   }
 * )
 */
class TealiumiqWidget extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * Tealium iQ service.
   *
   * @var \Drupal\tealiumiq\Service\Tealiumiq
   */
  private $tealiumiq;

  /**
   * TagPluginManager.
   *
   * @var \Drupal\tealiumiq\Service\TagPluginManager
   */
  private $tagPluginManager;

  /**
   * ConfigFactoryInterface.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container,
                                array $configuration,
                                $plugin_id,
                                $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('tealiumiq.tealiumiq'),
      $container->get('plugin.manager.tealiumiq.tag'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id,
                              $plugin_definition,
                              FieldDefinitionInterface $field_definition,
                              array $settings,
                              array $third_party_settings,
                              Tealiumiq $tealiumiq,
                              TagPluginManager $tagPluginManager,
                              ConfigFactoryInterface $configFactory) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->tealiumiq = $tealiumiq;
    $this->tagPluginManager = $tagPluginManager;
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items,
                              $delta,
                              array $element,
                              array &$form,
                              FormStateInterface $form_state) {
    $item = $items[$delta];

    // Retrieve the values for each tealiumiq tag from the serialized array.
    $values = [];
    if (!empty($item->value)) {
      $values = unserialize($item->value);
    }

    // Get default Tealium iQ tags.
    $defaults = $this->configFactory->get('tealiumiq.defaults')->get();

    // Populate fields which have not been overridden in the entity.
    if (!empty($defaults)) {
      foreach ($defaults as $tagId => $tagValue) {
        if (!isset($values[$tagId]) && !empty($tagValue)) {
          $values[$tagId] = $tagValue;
        }
      }
    }

    // Find the current entity type and bundle.
    $entity_type = $item->getEntity()->getentityTypeId();
    $element = $this->tealiumiq->form($values, $element, [$entity_type]);

    // Put the form element into the form's "advanced" group.
    $element['#group'] = 'advanced';

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // Flatten the values array to remove the groups and then serialize all the
    // tealiumiq tags into one value for storage.
    foreach ($values as &$value) {
      $flattened_value = [];
      foreach ($value as $group) {
        // Exclude the "original delta" value.
        if (is_array($group)) {
          foreach ($group as $tag_id => $tag_value) {
            $tag = $this->tagPluginManager->createInstance($tag_id);
            $tag->setValue($tag_value);
            if (!empty($tag->value())) {
              $flattened_value[$tag_id] = $tag->value();
            }
          }
        }
      }
      $value = serialize($flattened_value);
    }

    return $values;
  }

}
