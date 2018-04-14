<?php

namespace Drupal\views_bulk_operations\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\views\Views;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\views_bulk_operations\ViewsBulkOperationsBatch;

/**
 * Defines VBO action processor.
 */
class ViewsBulkOperationsActionProcessor implements ViewsBulkOperationsActionProcessorInterface {

  use StringTranslationTrait;

  /**
   * View data provider service.
   *
   * @var \Drupal\views_bulk_operations\Service\ViewsbulkOperationsViewDataInterface
   */
  protected $viewDataService;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * VBO action manager.
   *
   * @var \Drupal\views_bulk_operations\Service\ViewsBulkOperationsActionManager
   */
  protected $actionManager;

  /**
   * Current user object.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $user;

  /**
   * Module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Is the object initialized?
   *
   * @var bool
   */
  protected $initialized = FALSE;

  /**
   * Definition of the processed action.
   *
   * @var array
   */
  protected $actionDefinition;

  /**
   * The processed action object.
   *
   * @var array
   */
  protected $action;

  /**
   * The current view object.
   *
   * @var \Drupal\views\ViewExecutable
   */
  protected $view;

  /**
   * View data from the bulk form.
   *
   * @var array
   */
  protected $bulkFormData;

  /**
   * Array of entities that will be processed in the current batch.
   *
   * @var array
   */
  protected $queue = [];

  /**
   * Constructor.
   *
   * @param \Drupal\views_bulk_operations\Service\ViewsbulkOperationsViewDataInterface $viewDataService
   *   View data provider service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   * @param \Drupal\views_bulk_operations\Service\ViewsBulkOperationsActionManager $actionManager
   *   VBO action manager.
   * @param \Drupal\Core\Session\AccountProxyInterface $user
   *   Current user object.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Module handler service.
   */
  public function __construct(
    ViewsbulkOperationsViewDataInterface $viewDataService,
    EntityTypeManagerInterface $entityTypeManager,
    ViewsBulkOperationsActionManager $actionManager,
    AccountProxyInterface $user,
    ModuleHandlerInterface $moduleHandler
  ) {
    $this->viewDataService = $viewDataService;
    $this->entityTypeManager = $entityTypeManager;
    $this->actionManager = $actionManager;
    $this->user = $user;
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * {@inheritdoc}
   */
  public function initialize(array $view_data, $view = NULL) {

    // It may happen that the service was already initialized
    // in this request (e.g. multiple Batch API operation calls).
    // Clear the processing queue in such a case.
    if ($this->initialized) {
      $this->queue = [];
    }

    if (!isset($view_data['configuration'])) {
      $view_data['configuration'] = [];
    }
    if (!empty($view_data['preconfiguration'])) {
      $view_data['configuration'] += $view_data['preconfiguration'];
    }

    // Initialize action object.
    $this->actionDefinition = $this->actionManager->getDefinition($view_data['action_id']);
    $this->action = $this->actionManager->createInstance($view_data['action_id'], $view_data['configuration']);

    // Set action context.
    $this->setActionContext($view_data);

    // Set entire view data as object parameter for future reference.
    $this->bulkFormData = $view_data;

    // Set the current view.
    $this->setView($view);

    $this->initialized = TRUE;
  }

  /**
   * Set the current view object.
   *
   * @param mixed $view
   *   The current view object or NULL.
   */
  protected function setView($view = NULL) {
    if (!is_null($view)) {
      $this->view = $view;
    }
    else {
      $this->view = Views::getView($this->bulkFormData['view_id']);
      $this->view->setDisplay($this->bulkFormData['display_id']);
    }
    $this->view->get_total_rows = TRUE;
    $this->view->views_bulk_operations_processor_built = TRUE;
    if (!empty($this->bulkFormData['arguments'])) {
      $this->view->setArguments($this->bulkFormData['arguments']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getPageList($page) {
    $list = [];

    $this->viewDataService->init($this->view, $this->view->getDisplay(), $this->bulkFormData['relationship_id']);

    $this->view->setItemsPerPage($this->bulkFormData['batch_size']);
    $this->view->setCurrentPage($page);
    $this->view->build();

    $offset = $this->bulkFormData['batch_size'] * $page;
    // If the view doesn't start from the first result,
    // move the offset.
    if ($pager_offset = $this->view->pager->getOffset()) {
      $offset += $pager_offset;
    }
    $this->view->query->setLimit($this->bulkFormData['batch_size']);
    $this->view->query->setOffset($offset);
    $this->moduleHandler->invokeAll('views_pre_execute', [$this->view]);
    $this->view->query->execute($this->view);

    $base_field = $this->view->storage->get('base_field');
    foreach ($this->view->result as $row) {
      $entity = $this->viewDataService->getEntity($row);

      // We don't need entity label here.
      $list[] = [
        $row->{$base_field},
        $entity->language()->getId(),
        $entity->getEntityTypeId(),
        $entity->id(),
      ];
    }

    return $list;
  }

  /**
   * {@inheritdoc}
   */
  public function populateQueue(array $list, array &$context = []) {
    // Determine batch size and offset.
    if (!empty($context)) {
      $batch_size = $this->bulkFormData['batch_size'];
      if (!isset($context['sandbox']['current_batch'])) {
        $context['sandbox']['current_batch'] = 0;
      }
      $current_batch = &$context['sandbox']['current_batch'];
      $offset = $current_batch * $batch_size;
    }
    else {
      $batch_size = 0;
      $current_batch = 0;
      $offset = 0;
    }

    if ($batch_size) {
      $batch_list = array_slice($list, $offset, $batch_size);
    }
    else {
      $batch_list = $list;
    }

    $base_field_values = [];
    foreach ($batch_list as $item) {
      $base_field_values[] = $item[0];
    }
    if (empty($base_field_values)) {
      return 0;
    }

    $this->view->setItemsPerPage(0);
    $this->view->setCurrentPage(0);
    $this->view->setOffset(0);
    $this->view->build();

    $base_field = $this->view->storage->get('base_field');
    $this->view->query->addWhere(0, $base_field, $base_field_values, 'IN');
    $this->view->query->build($this->view);

    // Execute the view.
    $this->moduleHandler->invokeAll('views_pre_execute', [$this->view]);
    $this->view->query->execute($this->view);

    // Get entities.
    $this->viewDataService->init($this->view, $this->view->getDisplay(), $this->bulkFormData['relationship_id']);
    foreach ($this->view->result as $row_index => $row) {
      // This may return rows for all possible languages.
      // Check if the current language is on the list.
      $found = FALSE;
      $entity = $this->viewDataService->getEntity($row);
      foreach ($batch_list as $delta => $item) {
        if ($row->{$base_field} === $item[0] && $entity->language()->getId() === $item[1]) {
          $this->queue[] = $entity;
          $found = TRUE;
          unset($batch_list[$delta]);
          break;
        }
      }
      if (!$found) {
        unset($this->view->result[$row_index]);
      }
    }

    // Extra processing when executed in a Batch API operation.
    if (!empty($context)) {
      if (!isset($context['sandbox']['total'])) {
        if (empty($list)) {
          $context['sandbox']['total'] = $this->viewDataService->getTotalResults();
        }
        else {
          $context['sandbox']['total'] = count($list);
        }
      }
      // Add batch size to context array for potential use in actions.
      $context['sandbox']['batch_size'] = $batch_size;
      $this->setActionContext($context);
    }

    if ($batch_size) {
      $current_batch++;
    }

    $this->setActionView();

    return count($this->queue);
  }

  /**
   * {@inheritdoc}
   */
  public function setActionContext(array $context) {
    if (isset($this->action) && method_exists($this->action, 'setContext')) {
      $this->action->setContext($context);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setActionView() {
    if (isset($this->action) && method_exists($this->action, 'setView')) {
      $this->action->setView($this->view);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function process() {
    $output = [];

    // Check if all queue items are actually Drupal entities.
    foreach ($this->queue as $delta => $entity) {
      if (!($entity instanceof EntityInterface)) {
        $output[] = $this->t('Skipped');
        unset($this->queue[$delta]);
      }
    }

    // Check entity type for multi-type views like search_api index.
    if (!empty($this->actionDefinition['type'])) {
      foreach ($this->queue as $delta => $entity) {
        if ($entity->getEntityTypeId() !== $this->actionDefinition['type']) {
          $output[] = $this->t('Entity type not supported');
          unset($this->queue[$delta]);
        }
      }
    }

    // Check access.
    foreach ($this->queue as $delta => $entity) {
      if (!$this->action->access($entity, $this->user)) {
        $output[] = $this->t('Access denied');
        unset($this->queue[$delta]);
      }
    }

    // Process queue.
    $results = $this->action->executeMultiple($this->queue);

    // Populate output.
    if (empty($results)) {
      $count = count($this->queue);
      for ($i = 0; $i < $count; $i++) {
        $output[] = $this->bulkFormData['action_label'];
      }
    }
    else {
      foreach ($results as $result) {
        $output[] = $result;
      }
    }
    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function executeProcessing(array &$data, $view = NULL) {
    if ($data['batch']) {
      $batch = ViewsBulkOperationsBatch::getBatch($data);
      batch_set($batch);
    }
    else {
      $list = $data['list'];

      // Populate and process queue.
      if (!$this->initialized) {
        $this->initialize($data, $view);
      }
      if (empty($list)) {
        $list = $this->getPageList(0);
      }
      if ($this->populateQueue($list)) {
        $batch_results = $this->process();
      }

      $results = ['operations' => []];
      foreach ($batch_results as $result) {
        $results['operations'][] = (string) $result;
      }
      ViewsBulkOperationsBatch::finished(TRUE, $results, []);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getQueue() {
    return $this->queue;
  }

}
