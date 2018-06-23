<?php

namespace Drupal\react_comments\Model;

use Drupal\comment\Entity\Comment as CommentEntity;

class Comment extends CommentBase {

  /**
   * Load an individual comment.
   */
  public function load($show_unpublished = FALSE) {
    $query = db_select('comment_field_data', 'c');
    $query->join('comment__comment_body', 'b', 'c.cid = b.entity_id');
    $query->fields('c', ['uid', 'pid', 'entity_id', 'subject', 'created', 'changed', 'status', 'hostname', 'name'])
      ->fields('b', ['comment_body_value'])
      ->condition('c.cid', $this->getId());
    $record = $query->execute()
      ->fetchObject();

    if (empty($record->comment_body_value)) return NULL;

    $user = new User();
    $user->setId($record->uid)->load();

    if ($status = $this->loadCustomStatus()) {
      $this->setStatus($status);
    }
    else {
      $this->setStatus($record->status);
    }

    $this->setReplyId($record->pid)
      ->setEntityId($record->entity_id)
      ->setUser($user)
      ->setName($record->name)
      ->setComment($record->comment_body_value)
      ->setIpAddress($record->hostname)
      ->setCreatedAt($record->created)
      ->setChangedAt($record->changed)
      ->setPublishedStatus($record->status);

    $replies = $this->loadReplies($this->getId(), $show_unpublished);
    $this->setReplies($replies);

    return $this;
  }

  /**
   * Save a comment.
   */
  public function save() {
    $comment = CommentEntity::create([
      // @todo entity_type shouldn't be hardcoded to node
      'entity_type'    => 'node',
      'entity_id'      => $this->getEntityId(),
      'pid'            => $this->getReplyId(),
      'field_name'     => 'comment',
      'uid'            => $this->getUser()->getId(),
      'comment_type'   => 'comment',
      'subject'        => $this->getSubject(),
      'comment_body'   => [
        'value'  => $this->getComment(),
        'format' => 'basic_html',
      ],
      'status'         => $this->getStatus(),
      'published'      => $this->isPublished(),
      'name'           => $this->getName()
    ]);
    $comment->save();
    $this->setId($comment->id())
      ->updateCustomStatus();
    return $this->getId();
  }

  /**
   * Update a comment.
   */
  public function update() {
    // @todo consider paring this whole class way down and relying more on core Comment entity.
    $status =  $this->getStatus();
    if (is_numeric($status)) {
      $comment = CommentEntity::load($this->getId());

      $comment->set('pid',        $this->getReplyId());
      $comment->set('entity_id',  $this->getEntityId());
      $comment->set('uid',        $this->getUser()->getId());
      $comment->set('subject',    $this->getSubject());
      $this->updateCustomStatus();
      if (is_numeric($this->customStatusToDrupalStatus($this->getStatus()))) {
        $comment->set('status', $this->customStatusToDrupalStatus($this->getStatus()));
      }
      else {
        $comment->set('status', $comment->getStatus());
      }
      $comment->set('comment_body', [
        'value'  => $this->getComment(),
        'format' => 'basic_html'
      ]);

      return $comment->save();
    }
    return NULL;
  }

 /**
  * Delete a comment.
  */
 public function delete() {
   $comment = CommentEntity::load($this->getId());
   $user = $this->getUser();
   if ( $this->isAdmin() || ($user->hasPermission('edit own comments') && ($comment->get('uid')->target_id == $user->getId())) ) {
     $this->setStatus( RC_COMMENT_DELETED )->update();
   }
   return NULL;
 }

  /**
   * Update additional comment status.
   */
  private function loadCustomStatus() {
    $query = db_select('react_comments_status', 's');
    $query->fields('s', ['status'])
      ->condition('s.cid', $this->getId());
    return $query->execute()
      ->fetchField();
  }

  /**
   * Update additional comment status.
   */
  private function updateCustomStatus() {
    db_merge('react_comments_status')
      ->key(['cid' => $this->getId()])
      ->fields([
        'cid'        => $this->getId(),
        'uid'        => $this->getUser()->getId(),
        'status'     => $this->getStatus(),
        'created_at' => time(),
      ])
      ->execute();
  }

  /**
   * Load a comment with its replies recursively.
   */
  private function loadReplies($pid = 0, $show_unpublished = FALSE) {
    $query = db_select('comment_field_data', 'c')
      ->fields('c')
      ->condition('c.pid', $pid)
      ->orderBy('c.created');

    if (!$show_unpublished) {
      $query->condition('c.status', 1);
    }

    $result = $query->execute();

    $thread = [];
    foreach ($result as $item) {
      if ($comment = (new Comment())->setId($item->cid)->load($show_unpublished)) {
        $thread[] = $comment->model();
        $this->loadReplies($item->cid, $show_unpublished);
      }
    }
    return $thread;
  }

  /**
   *  Convert react comments custom status into drupal published status.
   *  Use with is_numeric to decide whether to change the drupal status.
   */
  private function customStatusToDrupalStatus($custom_status) {
    switch($custom_status) {
      case RC_COMMENT_PUBLISHED:
      case RC_COMMENT_DELETED:
        return 1;
      case RC_COMMENT_UNPUBLISHED:
        return 0;
      case RC_COMMENT_FLAGGED:
      default:
        // changing to flagged state shouldn't effect whether the comment is published or unpublished
        return NULL;
    }
  }

}
