<?php

namespace Drupal\react_comments\Model;

class CommentsBase extends Base {

  protected $entity_id;
  protected $comments;

  public function setEntityId($id) {
    $this->entity_id = $id;
    return $this;
  }

  public function getEntityId() {
    return (int) $this->entity_id;
  }

  public function setComments($comments) {
    $this->comments = $comments;
    return $this;
  }

  public function getComments() {
    return $this->comments;
  }

  public function model() {
    return $this->getComments();
  }

}
