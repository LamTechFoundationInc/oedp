<?php

namespace Drupal\social_post_facebook\Plugin\Network;

use Drupal\social_post\Plugin\Network\SocialPostNetworkInterface;

/**
 * Defines an interface for Facebook Post Network Plugin.
 */
interface FacebookPostInterface extends SocialPostNetworkInterface {

  /**
   * Gets the absolute url of the callback.
   *
   * @return string
   *   The callback url.
   */
  public function getOauthCallback();

  /**
   * Wrapper for post method.
   *
   * @param string $access_token
   *   The access token.
   * @param string $status
   *   The tweet text.
   */
  public function doPost($access_token, $status);

  /**
   * Gets a FacebookOAuth instance with oauth_token and oauth_token_secret.
   *
   * This method creates the SDK object by also passing the oauth_token and
   * oauth_token_secret. It is used for getting permanent tokens from
   * Facebook and authenticating users that has already granted permission.
   *
   * @return \Abraham\FacebookOAuth\FacebookOAuth
   *   The instance of the connection to Facebook.
   */
  public function getSdk2();

}
