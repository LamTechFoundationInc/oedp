<?php

namespace Drupal\social_post_facebook\Settings;

use Drupal\social_api\Settings\SettingsBase;

/**
 * Returns the app information.
 */
class FacebookPostSettings extends SettingsBase implements FacebookPostSettingsInterface {

  /**
   * APP ID.
   *
   * @var string
   */
  protected $appId;

  /**
   * APP Secret.
   *
   * @var string
   */
  protected $appSecret;

  /**
   * {@inheritdoc}
   */
  public function getAppId() {
    if (!$this->appId) {
      $this->appId = $this->config->get('app_id');
    }

    return $this->appId;
  }

  /**
   * {@inheritdoc}
   */
  public function getAppSecret() {
    if (!$this->appSecret) {
      $this->appSecret = $this->config->get('app_secret');
    }

    return $this->appSecret;
  }

}
