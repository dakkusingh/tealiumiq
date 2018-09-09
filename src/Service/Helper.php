<?php

namespace Drupal\tealiumiq\Service;

use Drupal\Component\Render\PlainTextOutput;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\views\ViewEntityInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class Tealiumiq Helper.
 *
 * @package Drupal\tealiumiq\Service
 */
class Helper {

  /**
   * EntityTypeManagerInterface.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * GroupPluginManager.
   *
   * @var \Drupal\tealiumiq\Service\GroupPluginManager
   */
  private $groupPluginManager;

  /**
   * TagPluginManager.
   *
   * @var \Drupal\tealiumiq\Service\TagPluginManager
   */
  private $tagPluginManager;

  /**
   * LoggerChannelFactoryInterface.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  private $logger;

  /**
   * Request Stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * Language Manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  private $languageManager;

  /**
   * Token Service.
   *
   * @var \Drupal\tealiumiq\Service\TealiumiqToken
   */
  private $tokenService;

  /**
   * RouteMatchInterface.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  private $routeMatch;

  /**
   * Helper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager Interface.
   * @param \Drupal\tealiumiq\Service\GroupPluginManager $groupPluginManager
   *   Group Plugin Manager.
   * @param \Drupal\tealiumiq\Service\TagPluginManager $tagPluginManager
   *   Tealiumiq Tag Plugin Manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $channelFactory
   *   Logger Channel Factory Interface.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   Request Stack.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   Language Manager Interface.
   * @param \Drupal\tealiumiq\Service\TealiumiqToken $token
   *   Tealiumiq Token.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   RouteMatchInterface.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager,
                              GroupPluginManager $groupPluginManager,
                              TagPluginManager $tagPluginManager,
                              LoggerChannelFactoryInterface $channelFactory,
                              RequestStack $requestStack,
                              LanguageManagerInterface $languageManager,
                              TealiumiqToken $token,
                              RouteMatchInterface $routeMatch) {
    $this->entityTypeManager = $entityTypeManager;
    $this->groupPluginManager = $groupPluginManager;
    $this->tagPluginManager = $tagPluginManager;
    $this->logger = $channelFactory->get('tealiumiq');
    $this->requestStack = $requestStack;
    $this->languageManager = $languageManager;
    $this->tokenService = $token;
    $this->routeMatch = $routeMatch;
  }

  /**
   * {@inheritdoc}
   */
  public function tagsFromEntity(ContentEntityInterface $entity) {
    $tags = [];

    $fields = $this->getFields($entity);

    /* @var \Drupal\field\Entity\FieldConfig $field_info */
    foreach ($fields as $field_name => $field_info) {
      // Get the tags from this field.
      $tags = $this->getFieldTags($entity, $field_name);
    }

    return $tags;
  }

  /**
   * Returns a list of the Tealium fields on an entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to examine.
   *
   * @return array
   *   The fields from the entity which are Tealium fields.
   */
  protected function getFields(ContentEntityInterface $entity) {
    $field_list = [];

    if ($entity instanceof ContentEntityInterface) {
      // Get a list of the metatag field types.
      $field_types = ['tealiumiq'];

      // Get a list of the field definitions on this entity.
      $definitions = $entity->getFieldDefinitions();

      // Iterate through all the fields looking for ones in our list.
      foreach ($definitions as $field_name => $definition) {
        // Get the field type, ie: metatag.
        $field_type = $definition->getType();

        // Check the field type against our list of fields.
        if (isset($field_type) && in_array($field_type, $field_types)) {
          $field_list[$field_name] = $definition;
        }
      }
    }

    return $field_list;
  }

  /**
   * Returns a list of the meta tags with values from a field.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The ContentEntityInterface object.
   * @param string $field_name
   *   The name of the field to work on.
   *
   * @return array
   *   Array of field tags.
   */
  protected function getFieldTags(ContentEntityInterface $entity, $field_name) {
    $tags = [];
    foreach ($entity->{$field_name} as $item) {
      // Get serialized value and break it into an array of tags with values.
      $serialized_value = $item->get('value')->getValue();
      if (!empty($serialized_value)) {
        $tags += unserialize($serialized_value);
      }
    }

    return $tags;
  }

  /**
   * Gets the group plugin definitions.
   *
   * @return array
   *   Group definitions.
   */
  protected function groupDefinitions() {
    return $this->groupPluginManager->getDefinitions();
  }

  /**
   * Gets the tag plugin definitions.
   *
   * @return array
   *   Tag definitions
   */
  protected function tagDefinitions() {
    return $this->tagPluginManager->getDefinitions();
  }

  /**
   * {@inheritdoc}
   */
  public function sortedGroups() {
    $tealiumiqGroups = $this->groupDefinitions();

    // Pull the data from the definitions into a new array.
    $groups = [];
    foreach ($tealiumiqGroups as $groupName => $groupInfo) {
      $groups[$groupName]['id'] = $groupInfo['id'];
      $groups[$groupName]['label'] = $groupInfo['label']->render();
      $groups[$groupName]['description'] = $groupInfo['description'];
      $groups[$groupName]['weight'] = $groupInfo['weight'];
    }

    // Create the 'sort by' array.
    $sortBy = [];
    foreach ($groups as $group) {
      $sortBy[] = $group['weight'];
    }

    // Sort the groups by weight.
    array_multisort($sortBy, SORT_ASC, $groups);

    return $groups;
  }

  /**
   * {@inheritdoc}
   */
  public function sortedTags() {
    $tealiumiqTags = $this->tagDefinitions();

    // Pull the data from the definitions into a new array.
    $tags = [];
    foreach ($tealiumiqTags as $tagName => $tagInfo) {
      $tags[$tagName]['id'] = $tagInfo['id'];
      $tags[$tagName]['label'] = $tagInfo['label']->render();
      $tags[$tagName]['group'] = $tagInfo['group'];
      $tags[$tagName]['weight'] = $tagInfo['weight'];
    }

    // Create the 'sort by' array.
    $sortBy = [];
    foreach ($tags as $key => $tag) {
      $sortBy['group'][$key] = $tag['group'];
      $sortBy['weight'][$key] = $tag['weight'];
    }

    // Sort the tags by weight.
    array_multisort($sortBy['group'], SORT_ASC, $sortBy['weight'], SORT_ASC, $tags);

    return $tags;
  }

  /**
   * {@inheritdoc}
   */
  public function sortedGroupsWithTags() {
    $groups = $this->sortedGroups();
    $tags = $this->sortedTags();

    foreach ($tags as $tagName => $tag) {
      $tagGroup = $tag['group'];

      if (!isset($groups[$tagGroup])) {
        // If the tag is claiming a group that has no matching plugin, log an
        // error and force it to the basic group.
        $this->logger->error(
          "Undefined group '%group' on tag '%tag'",
          ['%group' => $tagGroup, '%tag' => $tagName]
        );

        $tag['group'] = 'page';
        $tagGroup = 'page';
      }

      $groups[$tagGroup]['tags'][$tagName] = $tag;
    }

    return $groups;
  }

  /**
   * Generate the actual tealiumiq tag values.
   *
   * @param array $tags
   *   The array of tags as plugin_id => value.
   * @param object $entity
   *   Optional entity object to use for token replacements.
   *
   * @return array
   *   Render array with tag elements.
   */
  public function generateRawElements(array $tags, $entity = NULL) {
    // Ignore the update.php path.
    $request = $this->requestStack->getCurrentRequest();
    if ($request->getBaseUrl() == '/update.php') {
      return [];
    }

    $rawTags = [];

    $tealiumiqTags = $this->tagPluginManager->getDefinitions();

    // Order the elements by weight first, as some systems like Facebook care.
    uksort($tags, function ($tagName_a, $tagName_b) use ($tealiumiqTags) {
      $weight_a = isset($tealiumiqTags[$tagName_a]['weight']) ? $tealiumiqTags[$tagName_a]['weight'] : 0;
      $weight_b = isset($tealiumiqTags[$tagName_b]['weight']) ? $tealiumiqTags[$tagName_b]['weight'] : 0;

      return ($weight_a < $weight_b) ? -1 : 1;
    });

    // Each element of the $values array is a tag with the tag plugin name as
    // the key.
    foreach ($tags as $tagName => $value) {
      // Check to ensure there is a matching plugin.
      if (isset($tealiumiqTags[$tagName])) {
        // Get an instance of the plugin.
        $tag = $this->tagPluginManager->createInstance($tagName);

        // Render any tokens in the value.
        $token_replacements = [];
        if ($entity) {
          if ($entity instanceof ContentEntityInterface) {
            $token_replacements = [$entity->getEntityTypeId() => $entity];
          }
        }

        // Set the value as sometimes the data needs massaging, such as when
        // field defaults are used for the Robots field, which come as an array
        // that needs to be filtered and converted to a string.
        // @see Robots::setValue()
        $tag->setValue($value);
        $langcode = $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();

        $processed_value = PlainTextOutput::renderFromHtml(
          htmlspecialchars_decode(
            $this->tokenService->replace(
              $tag->value(),
              $token_replacements,
              ['langcode' => $langcode]
            )
          )
        );

        // Now store the value with processed tokens back into the plugin.
        $tag->setValue($processed_value);

        // Have the tag generate the output based on the value we gave it.
        $output = $tag->output();

        if (!empty($output)) {
          $output = $tag->multiple() ? $output : [$output];
          foreach ($output as $index => $element) {
            // Add index to tag name as suffix to avoid having same key.
            $index_tag_name = $tag->multiple() ? $tagName . '_' . $index : $tagName;
            $rawTags[$index_tag_name] = $element;
          }
        }
      }
    }

    return $rawTags;
  }

  /**
   * Load the tags by processing the route parameters.
   *
   * @param object $entity
   *   Entity if defined.
   *
   * @return array
   *   Tags if found.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function tagsFromRoute($entity = NULL) {
    if (!$entity) {
      $entity = $this->routeEntity();
    }

    if (!empty($entity) && $entity instanceof ContentEntityInterface) {
      // If content entity does not have an ID the page is likely an "Add" page,
      // so do not generate tealiumiq tags for entity which has not been created yet.
      if (!$entity->id()) {
        return NULL;
      }

      foreach ($this->tagsFromEntity($entity) as $tag => $data) {
        $tealiumiqTags[$tag] = $data;
      }
    }

    return $this->generateRawElements($tealiumiqTags, $entity);
  }

  /**
   * Return the Entity from route.
   *
   * @return mixed|null
   */
  public function routeEntity() {
    // If the current route has no parameters, return.
    if (!($route = $this->routeMatch
        ->getRouteObject()) || !($parameters = $route
        ->getOption('parameters'))) {
      return;
    }

    // Determine if the current route represents an entity.
    foreach ($parameters as $name => $options) {
      if (!isset($options['type']) || strpos($options['type'], 'entity:') !== 0) {
        continue;
      }

      $entity = $this->routeMatch->getParameter($name);
      if ($entity instanceof ContentEntityInterface) {
        return $entity;
      }

      // Since entity was found, no need to iterate further.
      return;
    }
  }
}
