<?php

namespace Drupal\views_bulk_operations\Service;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Views;
use Drupal\views\ResultRow;
use Drupal\Core\TypedData\TranslatableInterface;
use Drupal\views_bulk_operations\ViewsBulkOperationsEvent;

/**
 * Gets Views data needed by VBO.
 */
class ViewsBulkOperationsViewData implements ViewsBulkOperationsViewDataInterface {

  /**
   * Event dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * Module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The current view.
   *
   * @var \Drupal\views\ViewExecutable
   */
  protected $view;

  /**
   * The realtionship ID.
   *
   * @var string
   */
  protected $relationship;

  /**
   * Views data concerning the current view.
   *
   * @var array
   */
  protected $data;

  /**
   * Entity type ids returned by this view.
   *
   * @var array
   */
  protected $entityTypeIds;

  /**
   * Entity getter data.
   *
   * @var array
   */
  protected $entityGetter;

  /**
   * Object constructor.
   *
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   The event dispatcher service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Module handler service.
   */
  public function __construct(
    EventDispatcherInterface $eventDispatcher,
    ModuleHandlerInterface $moduleHandler
  ) {
    $this->eventDispatcher = $eventDispatcher;
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, $relationship) {
    $this->view = $view;
    $this->displayHandler = $display;
    $this->relationship = $relationship;

    // Get view entity types and results fetcher callable.
    $event = new ViewsBulkOperationsEvent($this->getViewProvider(), $this->getData(), $view);
    $this->eventDispatcher->dispatch(ViewsBulkOperationsEvent::NAME, $event);
    $this->entityTypeIds = $event->getEntityTypeIds();
    $this->entityGetter = $event->getEntityGetter();
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeIds() {
    return $this->entityTypeIds;
  }

  /**
   * Helper function to get data of the current view.
   *
   * @return array
   *   Part of views data that refers to the current view.
   */
  protected function getData() {
    if (!$this->data) {
      $viewsData = Views::viewsData();
      if (!empty($this->relationship) && $this->relationship != 'none') {
        $relationship = $this->displayHandler->getOption('relationships')[$this->relationship];
        $table_data = $viewsData->get($relationship['table']);
        $this->data = $viewsData->get($table_data[$relationship['field']]['relationship']['base']);
      }
      else {
        $this->data = $viewsData->get($this->view->storage->get('base_table'));
      }
    }
    return $this->data;
  }

  /**
   * {@inheritdoc}
   */
  public function getViewProvider() {
    $views_data = $this->getData();
    if (isset($views_data['table']['provider'])) {
      return $views_data['table']['provider'];
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getViewBaseField() {
    $views_data = $this->getData();
    if (isset($views_data['table']['base']['field'])) {
      return $views_data['table']['base']['field'];
    }
    throw new \Exception('Unable to get base field for the view.');
  }

  /**
   * {@inheritdoc}
   */
  public function getEntity(ResultRow $row) {
    if (!empty($this->entityGetter['file'])) {
      require_once $this->entityGetter['file'];
    }
    if (is_callable($this->entityGetter['callable'])) {
      return call_user_func($this->entityGetter['callable'], $row, $this->relationship, $this->view);
    }
    else {
      if (is_array($this->entityGetter['callable'])) {
        if (is_object($this->entityGetter['callable'][0])) {
          $info = get_class($this->entityGetter['callable'][0]);
        }
        else {
          $info = $this->entityGetter['callable'][0];
        }
        $info .= '::' . $this->entityGetter['callable'][1];
      }
      else {
        $info = $this->entityGetter['callable'];
      }
      throw new \Exception(sprintf("Entity getter method %s doesn't exist.", $info));
    }
  }

  /**
   * Get the total count of results on all pages.
   *
   * TODO: Find a way to get total number of results with mini pager.
   *
   * @return int
   *   The total number of results this view displays.
   */
  public function getTotalResults() {
    // This number is not correct in $this->view->total_rows for
    // standard entity views and different pagers, so we have to build
    // a custom count query in such a case.
    if (isset($this->view->query)) {
      $query = $this->view->query->query();
    }
    if (!empty($query)) {
      $total_results = $query->countQuery()->execute()->fetchField();
    }
    else {
      if (isset($this->view->query) && empty($this->view->result)) {
        // Let modules modify the view just prior to executing it.
        $this->moduleHandler->invokeAll('views_pre_execute', [$this->view]);
        $this->view->query->execute($this->view);
      }
      $total_results = $this->view->total_rows;
    }

    // Include pager offset.
    if ($total_results && isset($this->view->pager) && $offset = $this->view->pager->getOffset()) {
      $total_results -= $offset;
    }

    return $total_results;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityDefault(ResultRow $row, $relationship_id, ViewExecutable $view) {
    if ($relationship_id == 'none') {
      if (!empty($row->_entity)) {
        $entity = $row->_entity;
      }
    }
    elseif (isset($row->_relationship_entities[$relationship_id])) {
      $entity = $row->_relationship_entities[$relationship_id];
    }
    else {
      throw new \Exception('Unexpected view result row structure.');
    }

    if ($entity->isTranslatable()) {

      // Try to find a field alias for the langcode.
      // Assumption: translatable entities always
      // have a langcode key.
      $language_field = '';
      $langcode_key = $entity->getEntityType()->getKey('langcode');
      $base_table = $view->storage->get('base_table');
      foreach ($view->query->fields as $field) {
        if (
          $field['field'] === $langcode_key && (
            empty($field['base_table']) ||
            $field['base_table'] === $base_table
          )
        ) {
          $language_field = $field['alias'];
          break;
        }
      }
      if (!$language_field) {
        $language_field = $langcode_key;
      }

      if ($entity instanceof TranslatableInterface && isset($row->{$language_field})) {
        return $entity->getTranslation($row->{$language_field});
      }
    }

    return $entity;
  }

}
