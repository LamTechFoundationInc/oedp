<?php

namespace Drupal\social_post_facebook;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Manages storage of Facebook User entities.
 */
class FacebookUserEntityManager {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * FacebookUserEntityManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager, AccountInterface $current_user) {
    $this->entityManager = $entity_manager;
    $this->currentUser = $current_user;
  }

  /**
   * Saves the user in the database as a Facebook user.
   *
   * @param array $access_token
   *   Array with long live tokens returned by Facebook.
   *
   * @return int
   *   The current drupal user id.
   */
  public function saveUser(array &$access_token) {

    $entity = $this->entityManager->getStorage('social_post_facebook_user');

    // Checks if the user has already granted permissions.
    $user = $entity->loadByProperties([
      'facebook_id' => $access_token['user_id'],
      'uid' => (int) $this->currentUser->id(),
    ]);

    if (!count($user) > 0) {
      $facebook_user = [
        'facebook_id' => $access_token['user_id'],
        'screen_name' => $access_token['user_id'],
        'token' => $access_token['token'],
        'uid' => (int) $this->currentUser->id(),
      ];

      $entity->create($facebook_user)->save();

      drupal_set_message($this->t('Facebook account was successfully registered'));
    }
    else {
      drupal_set_message($this->t('This user has already granted permission for the facebook account'), 'warning');
    }

    return $this->currentUser->id();
  }

}
