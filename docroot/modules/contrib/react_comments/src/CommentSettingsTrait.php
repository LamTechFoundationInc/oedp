<?php

namespace Drupal\react_comments;

use Drupal\node\Entity\Node;

/**
 * Provides a method to check drupal comment settings (open/closed/hidden)
 */
trait CommentSettingsTrait {

  function getCommentSettings($nid) {
    $node = Node::load($nid);

    $status = $node->comment->status;

    if ($status === '1') {
      return 'closed';
    }
    else if ($status === '2') {
      return 'open';
    }
    else {
      // shouldn't need this as comment module handles hidden comments already
      return 'hidden';
    }
  }
}