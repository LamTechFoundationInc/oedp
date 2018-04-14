<?php

namespace Drupal\checklistapi\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

/**
 * Controller for Checklist API.
 */
class ChecklistapiController extends ControllerBase {

  /**
   * Returns the Checklists report.
   *
   * @return array
   *   Returns a render array.
   */
  public function report() {
    // Define table header.
    $header = [
      ['data' => t('Checklist')],
      [
        'data' => t('Progress'),
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data' => t('Last updated'),
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data' => t('Last updated by'),
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
      ['data' => t('Operations')],
    ];

    // Build table rows.
    $rows = [];
    $definitions = checklistapi_get_checklist_info();
    foreach ($definitions as $id => $definition) {
      $checklist = checklistapi_checklist_load($id);
      $row = [];
      $row[] = [
        'data' => ($checklist->userHasAccess()) ? \Drupal::l($checklist->title, $checklist->getUrl()) : drupal_placeholder($checklist->title),
        'title' => (!empty($checklist->description)) ? $checklist->description : '',
      ];
      $row[] = t('@completed of @total (@percent%)', [
        '@completed' => $checklist->getNumberCompleted(),
        '@total' => $checklist->getNumberOfItems(),
        '@percent' => round($checklist->getPercentComplete()),
      ]);
      $row[] = $checklist->getLastUpdatedDate();
      $row[] = $checklist->getLastUpdatedUser();
      if ($checklist->userHasAccess('edit') && $checklist->hasSavedProgress()) {
        $row[] = [
          'data' => [
            '#type' => 'operations',
            '#links' => [
              'clear' => [
                'title' => t('Clear'),
                'url' => Url::fromRoute($checklist->getRouteName() . '.clear', [], [
                  'query' => $this->getDestinationArray(),
                ]),
              ],
            ],
          ],
        ];
      }
      else {
        $row[] = '';
      }
      $rows[] = $row;
    }

    // Compile output.
    $output['table'] = [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => t('No checklists available.'),
    ];

    return $output;
  }

}
