<?php

namespace Drupal\social_post_facebook\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Facebook user entity entity.
 *
 * @ingroup social_post_facebook
 *
 * @ContentEntityType(
 *   id = "social_post_facebook_user",
 *   label = @Translation("Social Post Facebook User"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\social_post_facebook\Entity\Controller\FacebookUserListBuilder",
 *
 *     "form" = {
 *       "delete" = "Drupal\social_post_facebook\Form\FacebookUserEntityDeleteForm"
 *     },
 *     "access" = "Drupal\social_post_facebook\FacebookUserAccessControlHandler",
 *   },
 *   list_cache_contexts = { "user" },
 *   base_table = "facebook_user",
 *   admin_permission = "administer social post facebook user entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "delete-form" = "/admin/config/social-api/social-post/facebook/users/{social_post_facebook_user}/delete",
 *     "collection" = "/admin/config/social-api/social-post/facebook/users"
 *   }
 * )
 */
class FacebookUser extends ContentEntityBase implements FacebookUserInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? NODE_PUBLISHED : NODE_NOT_PUBLISHED);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getFacebookId() {
    return $this->get('facebook_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getScreenName() {
    return $this->get('screen_name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getUserId() {
    return (int) $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getAccessToken() {
    return $this->get('token')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getAccessTokenSecret() {
    return $this->get('token_secret')->value;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Facebook User entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID Facebook User entity.'))
      ->setReadOnly(TRUE);

    // The facebook user id.
    $fields['facebook_id'] = BaseFieldDefinition::create('string')
      ->setSetting('max_length', '20')
      ->setLabel(t('Facebook ID'))
      ->setDescription(t('The Facebook user id'));

    // The screen name of the user on Facebook.
    $fields['screen_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Screen Name'))
      ->setDescription(t('The Facebook screen name'));

    // The user id field.
    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User ID'))
      ->setDescription(t('The ID of the associated user.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'author',
        'weight' => -3,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ],
        'weight' => -3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Facebook oauth token.
    $fields['token'] = BaseFieldDefinition::create('string')
      ->setLabel(t('OAuth Token'))
      ->setDescription(t('The OAuth Token assigned for this user.'));

    // Facebook oauth token secret.
    $fields['token_secret'] = BaseFieldDefinition::create('string')
      ->setLabel(t('OAuth Token Secret'))
      ->setDescription(t('The OAuth Token Secret assigned for this user.'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
