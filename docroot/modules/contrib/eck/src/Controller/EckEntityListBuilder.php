<?php

namespace Drupal\eck\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Provides a list controller for ECK entity.
 *
 * @ingroup eck
 */
class EckEntityListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build['description'] = ['#markup' => $this->t('Entity settings')];
    $build['table'] = parent::render();

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['title'] = $this->t('Title');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $entityTypeId = $entity->getEntityTypeId();
    $route = "entity.{$entityTypeId}.canonical";
    $routeArguments = [$entityTypeId => $entity->id()];

    $row['id'] = $entity->id();
    $row['title'] = new Link($entity->label(), Url::fromRoute($route, $routeArguments));

    return array_merge($row, parent::buildRow($entity));
  }

}
