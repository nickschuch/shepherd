<?php

/**
 * @file
 * Contains Drupal\ua_sm_custom\Service\Password.
 */

namespace Drupal\ua_sm_custom\Service;

/**
 * Provides a password service.
 *
 * @package Drupal\ua_sm_custom
 */
class Password {

  /**
   * Generates a password of 20 random alphanumeric characters.
   *
   * @return string $password
   *   The generated password.
   */
  public function generate() {
    $count = range(0, 20);
    $random_alphanum = function($num) {
      $chars = array_merge(range('A', 'Z'), range('a', 'z'), range(0, 9));
      return $chars[array_rand($chars)];
    };
    return implode(array_map($random_alphanum, $count));
  }
}
