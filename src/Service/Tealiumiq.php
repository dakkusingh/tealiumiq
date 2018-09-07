<?php

namespace Drupal\tealiumiq\Service;

use Drupal\Component\Render\PlainTextOutput;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\views\ViewEntityInterface;
use Symfony\Component\HttpFoundation\RequestStack;

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
  public $udo;

  /**
   * Tag Plugin Manager.
   *
   * @var \Drupal\tealiumiq\Service\TagPluginManager
   */
  protected $tagPluginManager;

  /**
   * Token Service.
   *
   * @var \Drupal\tealiumiq\Service\TealiumiqToken
   */
  private $tokenService;

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
   * Group Plugin Manager.
   *
   * @var \Drupal\tealiumiq\Service\GroupPluginManager
   */
  private $groupPluginManager;

  /**
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface|\Drupal\tealiumiq\Service\LoggerChannelFactoryInterface
   */
  private $channelFactory;

  /**
   * Tealiumiq constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config
   *   Config Factory.
   * @param \Drupal\tealiumiq\Service\Udo $udo
   *   UDO Service.
   * @param \Drupal\tealiumiq\Service\TealiumiqToken $token
   *   Tealiumiq Token.
   * @param \Drupal\tealiumiq\Service\GroupPluginManager $groupPluginManager
   *   Group Plugin Manager.
   * @param \Drupal\tealiumiq\Service\TagPluginManager $tagPluginManager
   *   Tealiumiq Tag Plugin Manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   Request Stack.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   Language Manager Interface.
   * @param \Drupal\tealiumiq\Service\LoggerChannelFactoryInterface $channelFactory
   */
  public function __construct(ConfigFactory $config,
                              Udo $udo,
                              TealiumiqToken $token,
                              GroupPluginManager $groupPluginManager,
                              TagPluginManager $tagPluginManager,
                              RequestStack $requestStack,
                              LanguageManagerInterface $languageManager,
                              LoggerChannelFactoryInterface $channelFactory) {
    // Get Tealium iQ Settings.
    $this->config = $config->get('tealiumiq.settings');

    // Tealium iQ Settings.
    $this->account = $this->config->get('account');
    $this->profile = $this->config->get('profile');
    $this->environment = $this->config->get('environment');
    $this->async = $this->config->get('async');

    $this->udo = $udo;
    $this->tagPluginManager = $tagPluginManager;
    $this->tokenService = $token;
    $this->requestStack = $requestStack;
    $this->languageManager = $languageManager;
    $this->groupPluginManager = $groupPluginManager;
    $this->logger = $channelFactory->get('tealiumiq');
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

  /**
   * Gets all data values.
   *
   * @return array
   *   All variables.
   */
  public function getProperties() {
    $properties = $this->udo->getProperties();
    $raw = $this->generateRawElements($properties);

    return $raw;
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
          // @todo This needs a better way of discovering the context.
          if ($entity instanceof ViewEntityInterface) {
            // Views tokens require the ViewExecutable, not the config entity.
            // @todo Can we move this into metatag_views somehow?
            $token_replacements = ['view' => $entity->getExecutable()];
          }
          elseif ($entity instanceof ContentEntityInterface) {
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
   * {@inheritdoc}
   */
  public function form(array $values,
                       array $element,
                       array $tokenTypes = [],
                       array $includedGroups = NULL,
                       array $includedTags = NULL) {
    // Add the outer fieldset.
    $element += [
      '#type' => 'details',
    ];

    $element += $this->tokenService->tokenBrowser($tokenTypes);

    $groupsAndTags = $this->sortedGroupsWithTags();

    foreach ($groupsAndTags as $groupName => $group) {
      // Only act on groups that have tags and are in the list of included
      // groups (unless that list is null).
      if (isset($group['tags']) && (is_null($includedGroups) || in_array($groupName, $includedGroups) || in_array($group['id'], $includedGroups))) {
        // Create the fieldset.
        $element[$groupName]['#type'] = 'details';
        $element[$groupName]['#title'] = $group['label'];
        $element[$groupName]['#description'] = $group['description'];
        $element[$groupName]['#open'] = TRUE;

        foreach ($group['tags'] as $tagName => $tag) {
          // Only act on tags in the included tags list, unless that is null.
          if (is_null($includedTags) ||
              in_array($tagName, $includedTags) ||
              in_array($tag['id'], $includedTags)) {
            // Make an instance of the tag.
            $tag = $this->tagPluginManager->createInstance($tagName);

            // Set the value to the stored value, if any.
            $tag_value = isset($values[$tagName]) ? $values[$tagName] : NULL;
            $tag->setValue($tag_value);

            // Open any groups that have non-empty values.
            if (!empty($tag_value)) {
              $element[$groupName]['#open'] = TRUE;
            }

            // Create the bit of form for this tag.
            $element[$groupName][$tagName] = $tag->form($element);
          }
        }
      }
    }

    return $element;
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

}
