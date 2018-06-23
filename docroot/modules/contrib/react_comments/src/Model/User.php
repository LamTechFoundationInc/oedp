<?php

namespace Drupal\react_comments\Model;

use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User as DrupalUser;

class User extends UserBase {

  public function __construct(AccountInterface $user = NULL) {
    if ($user) {
      $this
        ->setId($user->id())
        ->setName($user->getUsername())
        ->setEmail($user->getEmail())
        ->setPermissions($this->loadPermissions($user));
    }
  }

  public function load() {
    $user = DrupalUser::load($this->getId());
    if ($user) {
      $this
        ->setId($user->id())
        ->setName($user->getUsername())
        ->setEmail($user->getEmail())
        ->setPermissions($this->loadPermissions($user));
    }
  }

  private function loadPermissions(AccountInterface $user) {
    $permissions = [
      'administer comments',
      'administer comment types',
      'access comments',
      'post comments',
      'skip comment approval',
      'edit own comments'
    ];
    foreach ($permissions as $key => $permission) {
      if (!$user->hasPermission($permission)) {
        unset($permissions[$key]);
      }
    }
    return $permissions;
  }
}
