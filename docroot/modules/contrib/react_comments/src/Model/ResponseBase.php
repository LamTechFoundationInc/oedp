<?php

namespace Drupal\react_comments\Model;

class ResponseBase {

  protected $data;
  protected $code;

  private $messages = [
    'success'                 => 'Success',
    'invalid_nid'             => 'Invalid Entity ID supplied',
    'nid_not_found'           => 'Entity ID not found',
    'invalid_cid'             => 'Invalid Comment ID supplied',
    'cid_not_found'           => 'Comment ID not found',
    'invalid_uid'             => 'Invalid User ID supplied',
    'uid_not_found'           => 'User ID not found',
    'no_comments_found'       => 'No comments found for supplied entity',
    'comment_deleted'         => 'Comment does not exist',
    'already_deleted'         => 'Comment was already deleted',
    'already_flagged'         => 'Comment was already flagged',
    'not_authorized'          => 'You are not authorized. Please login',
    'queued_for_moderation'   => 'Your comment has been queued for moderation',
    'comments_closed'         => 'Comments are closed for this post',
    'comments_hidden'         => 'Comments are disabled for this post'
  ];

  public function setData($data) {
    $this->data = $data;
    return $this;
  }

  public function getData() {
    return $this->data;
  }

  public function setCode($code) {
    $this->code = $code;
    return $this;
  }

  public function getCode() {
    return $this->code;
  }

  public function getMessage() {
    return !empty($this->messages[$this->getCode()])
      ? $this->messages[$this->getCode()]
      : 'Unknown error occured';
  }

  public function model() {
    return [
      'code'    => $this->getCode(),
      'message' => $this->getMessage(),
      'data'    => $this->getData(),
    ];
  }

}
