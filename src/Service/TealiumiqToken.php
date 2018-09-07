<?php

namespace Drupal\tealiumiq\Service;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Utility\Token;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Token handling service. Uses core token service or contributed Token.
 */
class TealiumiqToken {

  use StringTranslationTrait;

  /**
   * Token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * Constructs a new TealiumiqToken object.
   *
   * @param \Drupal\Core\Utility\Token $token
   *   Token service.
   */
  public function __construct(Token $token) {
    $this->token = $token;
  }

  /**
   * Wrapper for the Token module's string parsing.
   *
   * @param string $string
   *   The string to parse.
   * @param array $data
   *   Arguments for token->replace().
   * @param array $options
   *   Any additional options necessary.
   * @param \Drupal\Core\Render\BubbleableMetadata|null $bubbleable_metadata
   *   (optional) An object to which static::generate() and the hooks and
   *   functions that it invokes will add their required bubbleable metadata.
   *
   * @return mixed|string
   *   The processed string.
   */
  public function replace($string,
                          array $data = [],
                          array $options = [],
                          BubbleableMetadata $bubbleable_metadata = NULL) {
    // Set default requirements for Tealiumiq tag
    // unless options specify otherwise.
    $options = $options + [
      'clear' => TRUE,
    ];

    $replaced = $this->token->replace($string, $data, $options, $bubbleable_metadata);

    // Ensure that there are no double-slash sequences due to empty token
    // values.
    $replaced = preg_replace('/(?<!:)(?<!)\/+\//', '/', $replaced);

    return $replaced;
  }

  /**
   * Gatekeeper function to direct to either the core or contributed Token.
   *
   * @param array $token_types
   *   The token types to filter the tokens list by. Defaults to an empty array.
   *
   * @return array
   *   If token module is installed, a popup browser plus a help text. If not
   *   only the help text.
   */
  public function tokenBrowser(array $token_types = []) {
    $form = [];

    $form['intro_text'] = [
      '#markup' => '<p>' . $this->t('<strong>Configure the Tealiumiq tags below.</strong>') . '</p>',
    ];

    // Normalize taxonomy tokens.
    if (!empty($token_types)) {
      $token_types = array_map(function ($value) {
        return stripos($value, 'taxonomy_') === 0 ? substr($value, strlen('taxonomy_')) : $value;
      }, (array) $token_types);
    }

    $form['tokens'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => $token_types,
      '#global_types' => TRUE,
      '#show_nested' => FALSE,
    ];

    return $form;
  }

}
