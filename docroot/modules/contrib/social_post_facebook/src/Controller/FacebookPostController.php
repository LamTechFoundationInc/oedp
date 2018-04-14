<?php

namespace Drupal\social_post_facebook\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\social_api\Plugin\NetworkManager;
use Drupal\social_post_facebook\FacebookPostAuthManager;
use Drupal\social_post_facebook\FacebookUserEntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zend\Diactoros\Response\RedirectResponse;
use Drupal\Core\Url;

/**
 * Manages requests to Facebook.
 */
class FacebookPostController extends ControllerBase {

  /**
   * The network plugin manager.
   *
   * @var \Drupal\social_api\Plugin\NetworkManager
   */
  protected $networkManager;

  /**
   * The facebook post auth manager.
   *
   * @var \Drupal\social_post_facebook\FacebookPostAuthManager
   */
  protected $authManager;

  /**
   * The Facebook user entity manager.
   *
   * @var \Drupal\social_post_facebook\FacebookUserEntityManager
   */
  protected $facebookEntity;

  /**
   * FacebookPostController constructor.
   *
   * @param \Drupal\social_api\Plugin\NetworkManager $network_manager
   *   The network plugin manager.
   * @param \Drupal\social_post_facebook\FacebookPostAuthManager $auth_manager
   *   The Facebook post auth manager.
   * @param \Drupal\social_post_facebook\FacebookUserEntityManager $facebook_entity
   *   The Facebook user entity manager.
   */
  public function __construct(NetworkManager $network_manager, FacebookPostAuthManager $auth_manager, FacebookUserEntityManager $facebook_entity) {
    $this->networkManager = $network_manager;
    $this->authManager = $auth_manager;
    $this->facebookEntity = $facebook_entity;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.network.manager'),
      $container->get('facebook_post.auth_manager'),
      $container->get('facebook_user_entity.manager')
    );
  }

  /**
   * Redirects user to Facebook for authentication.
   *
   * @return \Zend\Diactoros\Response\RedirectResponse
   *   Redirects to Facebook.
   *
   * @throws \Abraham\FacebookOAuth\FacebookOAuthException
   */
  public function redirectToFacebook() {
    /* @var \Drupal\social_post_facebook\Plugin\Network\FacebookPost $network_plugin */
    $network_plugin = $this->networkManager->createInstance('social_post_facebook');

    /* @var \Facebook\Facebook $connection */
    $connection = $network_plugin->getSdk();
    $helper = $connection->getRedirectLoginHelper();
    $loginUrl = Url::fromRoute('social_post_facebook.callback');
    $loginUrl->setAbsolute(TRUE);
    // Optional permissions.
    $permissions = ['publish_actions'];
    $url = $helper->getLoginUrl($loginUrl->toString(), $permissions);

    return new RedirectResponse($url);
  }

  /**
   * Callback function for the authentication process.
   *
   * @throws \Abraham\FacebookOAuth\FacebookOAuthException
   */
  public function callback() {

    /* @var \Abraham\FacebookOAuth\FacebookOAuth $connection */
    $connection = $this->networkManager->createInstance('social_post_facebook')->getSdk2();
    $helper = $connection->getRedirectLoginHelper();

    // Gets the permanent access token.
    $access_token = $helper->getAccessToken();
    $oAuth2Client = $connection->getOAuth2Client();
    $tokenMetadata = $oAuth2Client->debugToken($access_token);

    if (!$access_token->isLongLived()) {
      $access_token = $oAuth2Client->getLongLivedAccessToken($access_token);
    }

    $data = [
      'user_id' => $tokenMetadata->getUserId(),
      'token' => (string) $access_token->getValue(),
      'screen_name' => $tokenMetadata->getUserId(),
    ];

    // Save the user authorization tokens and store the current user id in $uid.
    $uid = $this->facebookEntity->saveUser($data);

    return $this->redirect('entity.user.edit_form', ['user' => $uid]);
  }

}
