<?php

namespace Drupal\tfa\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Component\Utility\Crypt;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides access control on the verification form.
 *
 * @package Drupal\tfa\Controller
 */
class TfaLoginController {

  /**
   * Denies access unless user matches hash value.
   *
   * @param \Drupal\Core\Routing\RouteMatch $route
   *   The route to be checked.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current logged in user, if any.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  public function access(RouteMatch $route, AccountInterface $account) {
    $user = $route->getParameter('user');
    $is_user = is_object($user) && ($user instanceof User);
    $allowed = ($is_user && ($this->getLoginHash($user) == $route->getParameter('hash')));
    if (!$allowed) {
      // Using not found prevents user id enumeration: response is same regardless of uid.
      throw new NotFoundHttpException();
    }
    if ($account->isAuthenticated()) {
      return $this->accessSelforAdmin($route, $account);
    }
    return AccessResult::allowed();
  }

  /**
   * Checks that current user is selected user or is admin.
   *
   * @param \Drupal\Core\Routing\RouteMatch $route
   *   The route to be checked.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Throws an exception when uid does not exist or hash does not validate.
   *   Using NotFound prevents uid enumeration.
   */
  public function accessSelfOrAdmin(RouteMatch $route, AccountInterface $account) {
    $target_user = $route->getParameter('user');
    $target_is_user = is_object($target_user) && ($target_user instanceof User);
    // Route requires login and a valid target user account.
    if (!$account->isAuthenticated() || !$target_is_user) {
      // 404 exception prevents uid enumeration on routes that use this check.
      throw new NotFoundHttpException();
    }
    // Logged in users may only work with other users if they have administer users permission.
    if ($target_user->id() != $account->id() && !$account->hasPermission('administer users')) {
      throw new NotFoundHttpException();
    }
    return AccessResult::allowed();
  }

  /**
   * Copied from TfaLoginForm.php.
   *
   * @param \Drupal\user\Entity\User $account
   *   The user account for which a hash is required.
   *
   * @return string
   *   The hash value representing the user.
   */
  protected function getLoginHash(User $account) {
    // Using account login will mean this hash will become invalid once user has
    // authenticated via TFA.
    $data = implode(':', [
      $account->getUsername(),
      $account->getPassword(),
      $account->getLastLoginTime(),
    ]);
    return Crypt::hashBase64($data);
  }

}
