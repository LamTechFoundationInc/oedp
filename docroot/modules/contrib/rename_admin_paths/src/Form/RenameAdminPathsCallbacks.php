<?php

namespace Drupal\rename_admin_paths\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * RenameAdminPathsCallbacks class
 */
class RenameAdminPathsCallbacks {

  /**
   * Form element validation handler for 'name' in form_test_validate_form().
   *
   * @param $element
   *   The field element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   */
  public function validatePath(&$element, FormStateInterface $form_state) {
    // Force path replacement values to contain only lowercase letters, numbers, and underscores.
    if (!empty($element['#value']) && !preg_match('!^[a-z0-9_]+$!i', $element['#value'])) {
      $form_state->setError($element, t('Path replacement value must contain only lowercase letters, numbers, and underscores.'));
    }
  }

}
