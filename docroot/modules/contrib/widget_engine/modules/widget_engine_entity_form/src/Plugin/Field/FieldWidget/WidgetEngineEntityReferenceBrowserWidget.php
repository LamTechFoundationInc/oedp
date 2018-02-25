<?php

namespace Drupal\widget_engine_entity_form\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\entity_browser\Element\EntityBrowserElement;
use Drupal\entity_browser\Plugin\Field\FieldWidget\EntityReferenceBrowserWidget;

/**
 * Plugin implementation of the 'entity_reference' widget for entity browser.
 *
 * @FieldWidget(
 *   id = "widget_entity_browser_entity_reference",
 *   label = @Translation("Widget entity browser"),
 *   description = @Translation("Uses entity browser to select entities."),
 *   multiple_values = TRUE,
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class WidgetEngineEntityReferenceBrowserWidget extends EntityReferenceBrowserWidget {

  /**
   * The depth of the delete button.
   *
   * This property exists so it can be changed if subclasses.
   *
   * @var int
   */
  protected static $deleteDepth = 5;

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $entity_type = $this->fieldDefinition->getFieldStorageDefinition()->getSetting('target_type');
    $entities = $this->formElementEntities($items, $element, $form_state);

    // Get correct ordered list of entity IDs.
    $ids = array_map(
      function (EntityInterface $entity) {
        return $entity->id();
      },
      $entities
    );

    // We store current entity IDs as we might need them in future requests. If
    // some other part of the form triggers an AJAX request with
    // #limit_validation_errors we won't have access to the value of the
    // target_id element and won't be able to build the form as a result of
    // that. This will cause missing submit (Remove, Edit, ...) elements, which
    // might result in unpredictable results.
    $form_state->set(['entity_browser_widget', $this->getFormStateKey($items)], $ids);

    $hidden_id = Html::getUniqueId('edit-' . $this->fieldDefinition->getName() . '-target-id');
    $details_id = Html::getUniqueId('edit-' . $this->fieldDefinition->getName());

    $element += [
      '#id' => $details_id,
      '#type' => 'details',
      '#open' => !empty($entities) || $this->getSetting('open'),
      '#required' => $this->fieldDefinition->isRequired(),
      // We are not using Entity browser's hidden element since we maintain
      // selected entities in it during entire process.
      'target_id' => [
        '#type' => 'hidden',
        '#id' => $hidden_id,
        // We need to repeat ID here as it is otherwise skipped when rendering.
        '#attributes' => [
          'id' => $hidden_id,
          'class' => 'widgets-list-wrapper',
        ],
        '#default_value' => implode(' ', array_map(
          function (EntityInterface $item) {
            return $item->getEntityTypeId() . ':' . $item->id();
          },
          $entities
        )),
        // #ajax is officially not supported for hidden elements but if we
        // specify event manually it works.
        '#ajax' => [
          'callback' => [get_class($this), 'updateWidgetCallback'],
          'wrapper' => $details_id,
          'event' => 'entity_browser_value_updated',
        ],
      ],
    ];

    // Get configuration required to check entity browser availability.
    $cardinality = $this->fieldDefinition->getFieldStorageDefinition()->getCardinality();
    $selection_mode = $this->getSetting('selection_mode');

    // Enable entity browser if requirements for that are fulfilled.
    if (EntityBrowserElement::isEntityBrowserAvailable($selection_mode, $cardinality, count($ids))) {
      $element['entity_browser'] = [
        '#type' => 'entity_browser',
        '#entity_browser' => $this->getSetting('entity_browser'),
        '#cardinality' => $cardinality,
        '#selection_mode' => $selection_mode,
        '#default_value' => $entities,
        '#entity_browser_validators' => ['entity_type' => ['type' => $entity_type]],
        '#custom_hidden_id' => $hidden_id,
        '#process' => [
          ['\Drupal\entity_browser\Element\EntityBrowserElement', 'processEntityBrowser'],
          [get_called_class(), 'processEntityBrowser'],
        ],
      ];

    }
    $element['#attached']['library'][] = 'entity_browser/entity_reference';
    $element['#attached']['library'][] = 'widget_engine_entity_form/entity_browser_widget_preview';
    $element['#attached']['drupalSettings']['tokens'] = [
      'token_preview' => \Drupal::csrfToken()->get('widgetTokenPreview'),
      'token_save' => \Drupal::csrfToken()->get('widgetTokenPreviewSave'),
    ];
    $element['#attributes']['class'][] = 'widgets-list-wrapper';

    $field_parents = $element['#field_parents'];

    $element['current'] = $this->displayCurrentSelection($details_id, $field_parents, $entities);

    return $element;
  }

  /**
   * AJAX form callback.
   */
  public static function updateWidgetCallback(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    // AJAX requests can be triggered by hidden "target_id" element when
    // entities are added or by one of the "Remove" buttons. Depending on that
    // we need to figure out where root of the widget is in the form structure
    // and use this information to return correct part of the form.
    if (!empty($trigger['#ajax']['event']) && $trigger['#ajax']['event'] == 'entity_browser_value_updated') {
      $parents = array_slice($trigger['#array_parents'], 0, -1);
    }
    elseif ($trigger['#type'] == 'submit' && strpos($trigger['#name'], '_remove_')) {
      $parents = array_slice($trigger['#array_parents'], 0, -static::$deleteDepth);
    }

    return NestedArray::getValue($form, $parents);
  }

  /**
   * Submit callback for remove buttons.
   */
  public static function removeItemSubmit(&$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    if (!empty($triggering_element['#attributes']['data-entity-id']) && isset($triggering_element['#attributes']['data-row-id'])) {
      $id = $triggering_element['#attributes']['data-entity-id'];
      $row_id = $triggering_element['#attributes']['data-row-id'];
      $parents = array_slice($triggering_element['#parents'], 0, -static::$deleteDepth);
      $array_parents = array_slice($triggering_element['#array_parents'], 0, -static::$deleteDepth);

      // Find and remove correct entity.
      $values = explode(' ', $form_state->getValue(array_merge($parents, ['target_id'])));
      foreach ($values as $index => $item) {
        if ($item == $id && $index == $row_id) {
          array_splice($values, $index, 1);

          break;
        }
      }
      $target_id_value = implode(' ', $values);

      // Set new value for this widget.
      $target_id_element = &NestedArray::getValue($form, array_merge($array_parents, ['target_id']));
      $form_state->setValueForElement($target_id_element, $target_id_value);
      NestedArray::setValue($form_state->getUserInput(), $target_id_element['#parents'], $target_id_value);

      // Rebuild form.
      $form_state->setRebuild();
    }
  }

  /**
   * Builds the render array for displaying the current results.
   *
   * @param string $details_id
   *   The ID for the details element.
   * @param string[] $field_parents
   *   Field parents.
   * @param \Drupal\Core\Entity\ContentEntityInterface[] $entities
   *   Array of referenced entities.
   *
   * @return array
   *   The render array for the current selection.
   */
  protected function displayCurrentSelection($details_id, array $field_parents, array $entities) {

    $field_widget_display = $this->fieldDisplayManager->createInstance(
      $this->getSetting('field_widget_display'),
      $this->getSetting('field_widget_display_settings') + ['entity_type' => $this->fieldDefinition->getFieldStorageDefinition()->getSetting('target_type')]
    );

    return [
      '#theme_wrappers' => ['container'],
      '#attributes' => ['class' => ['entities-list']],
      'items' => array_map(
        function (ContentEntityInterface $entity, $row_id) use ($field_widget_display, $details_id, $field_parents) {
          $display = $field_widget_display->view($entity);
          $edit_button_access = $this->getSetting('field_widget_edit');
          if ($entity->getEntityTypeId() == 'file') {
            // On file entities, the "edit" button shouldn't be visible unless
            // the module "file_entity" is present, which will allow them to be
            // edited on their own form.
            $edit_button_access &= $this->moduleHandler->moduleExists('file_entity');
          }
          if (is_string($display)) {
            $display = ['#markup' => $display];
          }
          return [
            '#theme_wrappers' => ['container'],
            '#attributes' => [
              'class' => ['item-container', Html::getClass($field_widget_display->getPluginId())],
              'data-entity-id' => $entity->getEntityTypeId() . ':' . $entity->id(),
              'data-row-id' => $row_id,
            ],
            'display' => $display,
            'operations' => [
              '#theme_wrappers' => ['container'],
              '#attributes' => [
                'class' => ['widget-actions'],
              ],
              'remove_button' => [
                '#type' => 'submit',
                '#value' => $this->t('Remove'),
                '#ajax' => [
                  'callback' => [get_class($this), 'updateWidgetCallback'],
                  'wrapper' => $details_id,
                ],
                '#submit' => [[get_class($this), 'removeItemSubmit']],
                '#name' => $this->fieldDefinition->getName() . '_remove_' . $entity->id() . '_' . $row_id . '_' . md5(json_encode($field_parents)),
                '#limit_validation_errors' => [array_merge($field_parents, [$this->fieldDefinition->getName()])],
                '#attributes' => [
                  'data-entity-id' => $entity->getEntityTypeId() . ':' . $entity->id(),
                  'data-row-id' => $row_id,
                ],
                '#access' => (bool) $this->getSetting('field_widget_remove'),
              ],
              'edit_button' => [
                '#type' => 'submit',
                '#value' => $this->t('Edit'),
                '#ajax' => [
                  'url' => Url::fromRoute(
                    'entity_browser.edit_form', [
                      'entity_type' => $entity->getEntityTypeId(),
                      'entity' => $entity->id(),
                    ]
                  ),
                  'options' => [
                    'query' => [
                      'details_id' => $details_id,
                    ],
                  ],
                ],
                '#access' => $edit_button_access,
              ],
            ],
          ];
        },
        $entities,
        empty($entities) ? [] : range(0, count($entities) - 1)
      ),
    ];
  }

  /**
   * Determines the entities used for the form element.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   The field item to extract the entities from.
   * @param array $element
   *   The form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   The list of entities for the form element.
   */
  protected function formElementEntities(FieldItemListInterface $items, array $element, FormStateInterface $form_state) {
    $entities = [];
    $entity_type = $this->fieldDefinition->getFieldStorageDefinition()->getSetting('target_type');
    $entity_storage = $this->entityTypeManager->getStorage($entity_type);

    // Find IDs from target_id element (it stores selected entities in form).
    // This was added to help solve a really edge casey bug in IEF.
    if (($target_id_entities = $this->getEntitiesByTargetId($element, $form_state)) !== FALSE) {
      return $target_id_entities;
    }

    // Determine if we're submitting and if submit came from this widget.
    $is_relevant_submit = FALSE;
    $trigger = $form_state->getTriggeringElement();
    // Can be triggered by hidden target_id element or "Remove" button.
    if ($trigger &&
      (end($trigger['#parents']) === 'target_id' || (end($trigger['#parents']) === 'remove_button'))) {
      $is_relevant_submit = TRUE;

      // In case there are more instances of this widget on the same page we
      // need to check if submit came from this instance.
      $field_name_key = end($trigger['#parents']) === 'target_id' ? 2 : static::$deleteDepth + 1;
      $field_name_key = count($trigger['#parents']) - $field_name_key;
      $is_relevant_submit &= ($trigger['#parents'][$field_name_key] === $this->fieldDefinition->getName()) &&
        (array_slice($trigger['#parents'], 0, count($element['#field_parents'])) == $element['#field_parents']);
    }

    if ($is_relevant_submit) {
      // Submit was triggered by hidden "target_id" element when entities were
      // added via entity browser.
      if (!empty($trigger['#ajax']['event']) && $trigger['#ajax']['event'] == 'entity_browser_value_updated') {
        $parents = $trigger['#parents'];
      }
      // Submit was triggered by one of the "Remove" buttons. We need to walk
      // few levels up to read value of "target_id" element.
      elseif ($trigger['#type'] == 'submit' && strpos($trigger['#name'], $this->fieldDefinition->getName() . '_remove_') === 0) {
        $parents = array_merge(array_slice($trigger['#parents'], 0, -static::$deleteDepth), ['target_id']);
      }

      if (isset($parents) && $value = $form_state->getValue($parents)) {
        $entities = EntityBrowserElement::processEntityIds($value);
        return $entities;
      }
      return $entities;
    }
    // IDs from a previous request might be saved in the form state.
    elseif ($form_state->has([
      'entity_browser_widget',
      $this->getFormStateKey($items),
    ])
    ) {
      $stored_ids = $form_state->get([
        'entity_browser_widget',
        $this->getFormStateKey($items),
      ]);
      $indexed_entities = $entity_storage->loadMultiple($stored_ids);

      // Selection can contain same entity multiple times. Since loadMultiple()
      // returns unique list of entities, it's necessary to recreate list of
      // entities in order to preserve selection of duplicated entities.
      foreach ($stored_ids as $entity_id) {
        if (isset($indexed_entities[$entity_id])) {
          $entities[] = $indexed_entities[$entity_id];
        }
      }
      return $entities;
    }
    // We are loading for for the first time so we need to load any existing
    // values that might already exist on the entity. Also, remove any leftover
    // data from removed entity references.
    else {
      foreach ($items as $item) {
        if (isset($item->target_id)) {
          $entity = $entity_storage->load($item->target_id);
          if (!empty($entity)) {
            $entities[] = $entity;
          }
        }
      }
      return $entities;
    }
  }

}
