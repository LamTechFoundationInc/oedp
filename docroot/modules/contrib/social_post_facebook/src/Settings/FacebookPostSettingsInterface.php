<?php

namespace Drupal\social_post_facebook\Settings;

/**
 * Defines an interface for Social Post Facebook settings.
 */
interface FacebookPostSettingsInterface {

  /**
   * Gets the app id.
   *
   * @return string
   *   The consumer key.
   */
  public function getAppId();

  /**
   * Gets the app secret.
   *
   * @return string
   *   The consumer secret.
   */
  public function getAppSecret();

}
