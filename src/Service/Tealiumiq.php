<?php

namespace Drupal\tealiumiq\Service;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\tealiumiq\Event\AlterUdoPropertiesEvent;
use Drupal\tealiumiq\Event\FinalAlterUdoPropertiesEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
   * LoggerChannelFactoryInterface.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  private $logger;

  /**
   * Tealium Helper.
   *
   * @var \Drupal\tealiumiq\Service\Helper
   */
  public $helper;

  /**
   * EventDispatcherInterface.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  private $eventDispatcher;

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
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $channelFactory
   *   Logger Channel Factory Interface.
   * @param \Drupal\tealiumiq\Service\Helper $helper
   *   Tealium Helper.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   EventDispatcherInterface.
   */
  public function __construct(ConfigFactory $config,
                              Udo $udo,
                              TealiumiqToken $token,
                              GroupPluginManager $groupPluginManager,
                              TagPluginManager $tagPluginManager,
                              RequestStack $requestStack,
                              LanguageManagerInterface $languageManager,
                              LoggerChannelFactoryInterface $channelFactory,
                              Helper $helper,
                              EventDispatcherInterface $eventDispatcher) {
    // Get Tealium iQ Settings.
    $this->config = $config->get('tealiumiq.settings');
    $this->globalConfig = $config->get('tealiumiq.defaults');

    // Tealium iQ Settings.
    $this->account = $this->config->get('account');
    $this->profile = $this->config->get('profile');
    $this->environment = $this->config->get('environment');

    $this->udo = $udo;
    $this->tagPluginManager = $tagPluginManager;
    $this->tokenService = $token;
    $this->requestStack = $requestStack;
    $this->languageManager = $languageManager;
    $this->groupPluginManager = $groupPluginManager;
    $this->logger = $channelFactory->get('tealiumiq');
    $this->helper = $helper;
    $this->eventDispatcher = $eventDispatcher;
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
   * Get UTAG embed URL.
   *
   * @return string
   *   UTAG embed value.
   */
  public function getUtagUrl() {
    $url = "//tags.tiqcdn.com/utag/" .
      $this->account .
      '/' . $this->profile .
      '/' . $this->environment .
      '/utag.js';

    return $url;
  }

  /**
   * Get async Value.
   *
   * @return bool
   *   async value.
   */
  public function getAsync() {
    $tagLoad = $this->config->get('tag_load');

    if ($tagLoad == 'async') {
      return TRUE;
    }
    elseif ($tagLoad == 'sync') {
      return FALSE;
    }
  }

  /**
   * Gets all data values.
   *
   * @return array
   *   All variables.
   */
  public function getProperties() {
    return $this->udo->getProperties();
  }

  /**
   * Export the UDO as JSON.
   *
   * @return string
   *   Json encoded output.
   */
  public function getPropertiesJson() {
    $jsonEncoded = $this->config->get('json_encoded');
    if ($jsonEncoded == 'php') {
      return json_encode($this->getProperties());
    }

    return Json::encode($this->getProperties());
  }

  /**
   * Set all data values.
   */
  public function setUdoPropertiesFromRoute() {
    // Get the tags from Route.
    // Set the tags in UDO.
    $this->setProperties($this->helper->tagsFromRoute());
  }

  /**
   * Set all data values.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Entity.
   */
  public function setUdoPropertiesFromEntity(ContentEntityInterface $entity) {
    // Get the tags from Route.
    // Set the tags in UDO.
    $this->setProperties($this->helper->tagsFromRoute($entity), $entity);
  }

  /**
   * Set Tealium Tags.
   *
   * @param array $properties
   *   Tags array.
   * @param \Drupal\Core\Entity\ContentEntityInterface|null $entity
   *   Entity.
   */
  public function setProperties(array $properties = [], ContentEntityInterface $entity = NULL) {
    $newProperties = [];
    $deferField = $this->config->get('defer_fields');

    if ($deferField == FALSE) {
      $newProperties = $properties;
    }

    // Are we allowed to use defaults?
    if ($this->config->get('defaults_everywhere') == TRUE) {
      // Get default values.
      $defaultValues = $this->getDefaultTagValues();
      $newProperties = array_merge($defaultValues, $newProperties);
    }

    // Allow other modules to property variables before we send it.
    $alterUDOPropertiesEvent = new AlterUdoPropertiesEvent(
      $this->udo->getNamespace(),
      $newProperties
    );

    $event = $this->eventDispatcher->dispatch(
      AlterUdoPropertiesEvent::UDO_ALTER_PROPERTIES,
      $alterUDOPropertiesEvent
    );

    // Altered properties.
    $tealiumiqTags = $event->getProperties();

    if ($deferField == TRUE) {
      $tealiumiqTags = array_merge($tealiumiqTags, $properties);
    }

    // Dont proceed if there are no tags.
    if (empty($tealiumiqTags)) {
      return;
    }

    // Process tokens.
    if (!$entity) {
      $entity = $this->helper->getEnityFromRoute();
    }

    if (!empty($entity) && $entity instanceof ContentEntityInterface) {
      if ($entity->id()) {
        $tealiumiqTagsTokenised = $this->helper->generateRawElements($tealiumiqTags, $entity);
      }
    }
    else {
      $tealiumiqTagsTokenised = $this->helper->generateRawElements($tealiumiqTags);
    }

    // Cleanup the tags to key value.
    $tealiumiqTagsTokenised = $this->helper->tokenisedTags($tealiumiqTagsTokenised);

    // Allow other modules to property variables before we send it.
    $finalAlterUDOPropertiesEvent = new FinalAlterUdoPropertiesEvent(
      $this->udo->getNamespace(),
      $tealiumiqTagsTokenised
    );

    $finalEvent = $this->eventDispatcher->dispatch(
      FinalAlterUdoPropertiesEvent::FINAL_UDO_ALTER_PROPERTIES,
      $finalAlterUDOPropertiesEvent
    );

    // Final Altered properties.
    $finalTealiumiqTagsTokenised = $finalEvent->getProperties();

    // Set the tags in UDO.
    $this->udo->setProperties($finalTealiumiqTagsTokenised);
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

    $groupsAndTags = $this->helper->sortedGroupsWithTags();

    foreach ($groupsAndTags as $groupName => $group) {
      // Only act on groups that have tags and are in the list of included
      // groups (unless that list is null).
      if (isset($group['tags']) && (is_null($includedGroups) ||
          in_array($groupName, $includedGroups) ||
          in_array($group['id'], $includedGroups))) {
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
   * Get default tag values.
   *
   * @return array
   *   Default tags array.
   */
  public function getDefaultTagValues() {
    // Get all global values.
    $defaults = $this->globalConfig->get();

    // Get all tags.
    $allTags = $this->helper->sortedTags();
    $values = [];

    // Make sure only valid tags are taken forward.
    foreach ($allTags as $tag) {
      if (array_key_exists($tag['id'], $defaults) && $defaults[$tag['id']] != NULL) {
        $values[$tag['id']] = $defaults[$tag['id']];
      }
    }

    return $values;
  }

}
