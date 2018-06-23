<?php

namespace Drupal\react_comments\Plugin\rest\resource;

use Drupal\react_comments\CommentSettingsTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Cache\Cache;
use Drupal\node\Entity\Node;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\react_comments\Model\User;
use Drupal\react_comments\Model\Comment as CommentModel;
use Drupal\react_comments\Model\Comments as CommentsModel;
use Drupal\react_comments\Model\Response as ResponseModel;
use Drupal\react_comments\Model\Request as RequestModel;

/**
 * Provides comments for a given node resource.
 *
 * @RestResource(
 *   id = "comments",
 *   label = @Translation("Comments"),
 *   uri_paths = {
 *     "canonical" = "/react-comments/comments/{entity_id}",
 *     "https://www.drupal.org/link-relations/create" = "/react-comments/comments/{entity_id}"
 *   }
 * )
 */
class Comments extends ResourceBase {

  use CommentSettingsTrait;

  public function get($entity_id = NULL) {
    $response = new ResponseModel();

    // @todo make this less specific, comments don't necessarily have to be on a node.
    $node = Node::load($entity_id);

    if (empty($entity_id)) {
      $response->setCode('invalid_nid');
    }
    else if ($this->getCommentSettings($entity_id) === 'hidden') {
      $response->setCode('comments_hidden');
      $res = (new ResourceResponse($response->model(), 403));
      if ($node) {
        $res->addCacheableDependency($node);
      }
      return $res;
    }
    else {
      if (!$node) {
        $response->setCode('nid_not_found');
      }
      else {
        $comments = new CommentsModel();

        $show_unpublished = \Drupal::currentUser()->hasPermission('administer comments');

        if ($items = $comments->setEntityId($entity_id)->load($show_unpublished)) {
          $response->setData($items->model())
            ->setCode('success');
        }
        else {
          $response->setCode('no_comments_found');
        }
      }
    }
    $res = (new ResourceResponse($response->model()));
    $res->getCacheableMetadata()->addCacheContexts(['user.permissions']);
    if ($node) {
      $res->addCacheableDependency($node);
    }
    return $res;
  }

  public function post($entity_id = NULL) {
    $response = new ResponseModel();
    $drupal_user = \Drupal::currentUser();

    if (empty($entity_id)) {
      $response->setCode('invalid_nid');
      return (new JsonResponse($response->model(), 404));
    }
    else if (($this->getCommentSettings($entity_id) === 'closed') && !$drupal_user->hasPermission('administer comments')) {
      $response->setCode('comments_closed');
      return (new JsonResponse($response->model(), 403));
    }
    else {
      // @todo make this less specific, comments don't necessarily have to be on a node.
      if ($node = Node::load($entity_id)) {

        $request = (new RequestModel())->parseContentJson();
        $comment = new CommentModel();
        $current_user = new User($drupal_user);
        $response->setCode('not_authorized');

        if (is_numeric($current_user->getId()) && $drupal_user->hasPermission('post comments')) {
          $comment_body = $request->getJsonVal('comment');

          $skip_approval = $drupal_user->hasPermission('skip comment approval');

          if ($skip_approval) {
            $comment->setStatus( RC_COMMENT_PUBLISHED );
          }
          else {
            $comment->setStatus( RC_COMMENT_UNPUBLISHED );
          }

          if ($current_user->getId() === 0) {
            $comment->setName($request->getJsonVal('anon_name'));
          }

          $comment->setEntityId($entity_id)
            ->setUser($current_user)
            ->setComment($comment_body);
          $comment->setReplyId($request->getJsonVal('reply_comment_id'));
          $comment_id = $comment->save();
          if ($comment_id) {
            $response->setData($comment->setId($comment_id)->load()->model())
              ->setCode('queued_for_moderation');
          }
          if ($skip_approval) {
            $response->setCode('success');
          }
        }

        // Invalidate node cache.
        Cache::invalidateTags($node->getCacheTags());
      }
      else {
        $response->setCode('nid_not_found');
      }
    }
    return (new JsonResponse($response->model()));
  }

}
