<?php

namespace Drupal\widget_engine_entity_form\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseDialogCommand;
use Drupal\Core\Ajax\OpenDialogCommand;
use Drupal\Core\Ajax\PrependCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Render\Element\StatusMessages;
use Drupal\entity_browser\Ajax\ValueUpdatedCommand;
use Symfony\Component\HttpFoundation\Request;
use Drupal\widget_engine_entity_form\Ajax\WidgetPreviewRebuildCommand;

/**
 * Returns responses for entity browser routes.
 */
class WidgetEntityBrowserController extends ControllerBase {

  /**
   * Return an Ajax dialog command for editing a referenced entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   An entity being edited.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The currently processing request.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   An Ajax response with a command for opening or closing the dialog
   *   containing the edit form.
   */
  public function entityBrowserEdit(EntityInterface $entity, Request $request) {
    // Build the entity edit form.
    $form_object = $this->entityTypeManager()->getFormObject($entity->getEntityTypeId(), 'edit');
    $form_object->setEntity($entity);
    $form_state = (new FormState())
      ->setFormObject($form_object)
      ->disableRedirect();
    // Building the form also submits.
    $form = $this->formBuilder()->buildForm($form_object, $form_state);

    // Return a response, depending on whether it's successfully submitted.
    if (!$form_state->isExecuted()) {
      // Return the form as a modal dialog.
      $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
      $title = $this->t('Edit entity @entity', ['@entity' => $entity->label()]);
      // Build options array.
      $options = ['modal' => TRUE];
      if (!$form_state->getErrors()) {
        $options += [
          'width' => 'auto',
          'height' => 'auto',
          'maxWidth' => '',
          'maxHeight' => '',
          'fluid' => 1,
          'autoResize' => 0,
          'resizable' => 0,
        ];
      }
      $response = AjaxResponse::create()->addCommand(new OpenDialogCommand('#' . $entity->getEntityTypeId() . '-' . $entity->id() . '-edit-dialog', $title, $form, $options));
      if ($form_state->getErrors()) {
        $response->addCommand(
          new PrependCommand('#' . $entity->getEntityTypeId() . '-' . $entity->id() . '-edit-dialog', StatusMessages::renderMessages())
        );
      }
      return $response;
    }
    else {
      // Return command for closing the modal.
      $response = AjaxResponse::create()->addCommand(new CloseDialogCommand('#' . $entity->getEntityTypeId() . '-' . $entity->id() . '-edit-dialog'));
      // Also refresh the widget if "details_id" is provided.
      $details_id = $request->query->get('details_id');
      if (!empty($details_id)) {
        $response->addCommand(new ValueUpdatedCommand($details_id));
      }

      // Rebuild preview image.
      if ($entity->getEntityTypeId() == 'widget') {
        $response->addCommand(new WidgetPreviewRebuildCommand($entity->id()));
      }

      return $response;
    }
  }

}
