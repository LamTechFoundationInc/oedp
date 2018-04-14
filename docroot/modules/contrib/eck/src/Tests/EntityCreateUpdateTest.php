<?php

namespace Drupal\eck\Tests;

use Drupal\Core\Url;

/**
 * Tests if eck entities are correctly created and updated
 *
 * @group Eck
 *
 * @codeCoverageIgnore because we don't have to test the tests
 */
class EntityCreateUpdateTest extends TestBase {

  public function setUp() {
    parent::setUp();

    $this->drupalLogin($this->drupalCreateUser([], [], TRUE));
  }

  public function testEntityCreationDoesNotResultInMismatchedEntityDefinitions() {
    $this->createEntityType([], 'TestType');

    $this->assertNoMismatchedFieldDefinitions();
  }

  public function testIfEntityUpdateDoesNotResultInMismatchedEntityDefinitions() {
    $this->createEntityType([], 'TestType');

    $routeArguments = ['eck_entity_type' => 'testtype'];
    $route = 'entity.eck_entity_type.edit_form';
    $edit = ['created' => FALSE];
    $submitButton = t('Update @type', ['@type' => 'TestType']);
    $this->drupalPostForm(Url::fromRoute($route, $routeArguments), $edit, $submitButton);

    $this->assertNoMismatchedFieldDefinitions();
  }

  public function assertNoMismatchedFieldDefinitions() {
    $this->drupalGet(Url::fromRoute('system.status'));
    $this->assertNoRaw('Mismatched entity and/or field definitions');
  }

}
