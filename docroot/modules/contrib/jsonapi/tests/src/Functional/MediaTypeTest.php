<?php

namespace Drupal\Tests\jsonapi\Functional;

use Drupal\Core\Url;
use Drupal\media\Entity\MediaType;

/**
 * JSON API integration test for the "MediaType" config entity type.
 *
 * @group jsonapi
 */
class MediaTypeTest extends ResourceTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['media'];

  /**
   * {@inheritdoc}
   */
  protected static $entityTypeId = 'media_type';

  /**
   * {@inheritdoc}
   */
  protected static $resourceTypeName = 'media_type--media_type';

  /**
   * {@inheritdoc}
   *
   * @var \Drupal\media\MediaTypeInterface
   */
  protected $entity;

  /**
   * {@inheritdoc}
   */
  protected function setUpAuthorization($method) {
    $this->grantPermissionsToTestedRole(['administer media types']);
  }

  /**
   * {@inheritdoc}
   */
  protected function createEntity() {
    // Create a "Camelids" media type.
    $camelids = MediaType::create([
      'name' => 'Camelids',
      'id' => 'camelids',
      'description' => 'Camelids are large, strictly herbivorous animals with slender necks and long legs.',
      'source' => 'file',
    ]);

    $camelids->save();

    return $camelids;
  }

  /**
   * {@inheritdoc}
   */
  protected function getExpectedDocument() {
    $self_url = Url::fromUri('base:/jsonapi/media_type/media_type/' . $this->entity->uuid())->setAbsolute()->toString(TRUE)->getGeneratedUrl();
    return [
      'jsonapi' => [
        'meta' => [
          'links' => [
            'self' => 'http://jsonapi.org/format/1.0/',
          ],
        ],
        'version' => '1.0',
      ],
      'links' => [
        'self' => $self_url,
      ],
      'data' => [
        'id' => $this->entity->uuid(),
        'type' => 'media_type--media_type',
        'links' => [
          'self' => $self_url,
        ],
        'attributes' => [
          'dependencies' => [],
          'description' => 'Camelids are large, strictly herbivorous animals with slender necks and long legs.',
          'field_map' => [],
          'id' => 'camelids',
          'label' => NULL,
          'langcode' => 'en',
          'new_revision' => FALSE,
          'queue_thumbnail_downloads' => FALSE,
          'source' => 'file',
          // @todo Remove the first line in favor of the 3 commented lines in https://www.drupal.org/project/jsonapi/issues/2942979
          // @codingStandardsIgnoreStart
          'source_configuration' => '',
//          'source_configuration' => [
//            'source_field' => '',
//          ],
          // @codingStandardsIgnoreEnd
          'status' => TRUE,
          'uuid' => $this->entity->uuid(),
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getPostDocument() {
    // @todo Update in https://www.drupal.org/node/2300677.
  }

}