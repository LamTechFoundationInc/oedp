<?php

namespace Drupal\social_post_facebook\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Facebook user entities.
 *
 * @ingroup social_post_facebook
 */
interface FacebookUserInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the Facebook user entity name.
   *
   * @return string
   *   Name of the Facebook user entity.
   */
  public function getName();

  /**
   * Sets the Facebook user entity name.
   *
   * @param string $name
   *   The Facebook user entity name.
   *
   * @return \Drupal\social_post_facebook\Entity\FacebookUserInterface
   *   The called Facebook user entity entity.
   */
  public function setName($name);

  /**
   * Gets the Facebook user entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Facebook user entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Facebook user entity creation timestamp.
   *
   * @param int $timestamp
   *   The Facebook user entity creation timestamp.
   *
   * @return \Drupal\social_post_facebook\Entity\FacebookUserInterface
   *   The called Facebook user entity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Facebook user entity published status indicator.
   *
   * Unpublished Facebook user entity are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Facebook user entity is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Facebook user entity.
   *
   * @param bool $published
   *   TRUE to set this Facebook user entity to published, FALSE to set it to
   *   unpublished.
   *
   * @return \Drupal\social_post_facebook\Entity\FacebookUserInterface
   *   The called Facebook user entity entity.
   */
  public function setPublished($published);

  /**
   * Returns the Facebook user id.
   *
   * @return int
   *   The Facebook user id.
   */
  public function getFacebookId();

  /**
   * Returns the Facebook screen name.
   *
   * @return string
   *   The facebook screen name.
   */
  public function getScreenName();

  /**
   * Returns the Drupal user id.
   *
   * @return string
   *   The user id.
   */
  public function getUserId();

  /**
   * Returns the access token.
   *
   * @return string
   *   The access token.
   */
  public function getAccessToken();

  /**
   * Returns the access token secret.
   *
   * @return string
   *   The access token secret.
   */
  public function getAccessTokenSecret();

}
