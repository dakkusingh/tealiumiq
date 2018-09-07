<?php

namespace Drupal\tealiumiq\Service;

use Drupal\Component\Render\PlainTextOutput;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
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
   * @var \Drupal\tealiumiq\Service\TealiumiqTagPluginManager
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
   * Tealiumiq constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config
   *   Config Factory.
   * @param \Drupal\tealiumiq\Service\Udo $udo
   *   UDO Service.
   * @param \Drupal\tealiumiq\Service\TealiumiqTagPluginManager $tagPluginManager
   *   Tealiumiq Tag Plugin Manager.
   * @param \Drupal\tealiumiq\Service\TealiumiqToken $token
   *   Tealiumiq Token.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   Request Stack.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   Language Manager Interface.
   */
  public function __construct(ConfigFactory $config,
                              Udo $udo,
                              TealiumiqTagPluginManager $tagPluginManager,
                              TealiumiqToken $token,
                              RequestStack $requestStack,
                              LanguageManagerInterface $languageManager) {
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
   * Generate the actual meta tag values.
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
    uksort($tags, function ($tag_name_a, $tag_name_b) use ($tealiumiqTags) {
      $weight_a = isset($tealiumiqTags[$tag_name_a]['weight']) ? $tealiumiqTags[$tag_name_a]['weight'] : 0;
      $weight_b = isset($tealiumiqTags[$tag_name_b]['weight']) ? $tealiumiqTags[$tag_name_b]['weight'] : 0;

      return ($weight_a < $weight_b) ? -1 : 1;
    });

    // Each element of the $values array is a tag with the tag plugin name as
    // the key.
    foreach ($tags as $tag_name => $value) {
      // Check to ensure there is a matching plugin.
      if (isset($tealiumiqTags[$tag_name])) {
        // Get an instance of the plugin.
        $tag = $this->tagPluginManager->createInstance($tag_name);

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
            $index_tag_name = $tag->multiple() ? $tag_name . '_' . $index : $tag_name;
            $rawTags[$index_tag_name] = $element;
          }
        }
      }
    }

    return $rawTags;
  }

}
