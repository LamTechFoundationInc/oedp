<?php

namespace Drupal\eck\Tests;

use Drupal\Core\Url;

/**
 * Tests eck's access control.
 *
 * @group Eck
 *
 * @codeCoverageIgnore because we don't have to test the tests
 */
class AccessTest extends TestBase {

  /** @var array */
  protected $entityTypeInfo;

  /** @var array */
  protected $bundleInfo;

  public function setUp() {
    parent::setUp();
    $this->entityTypeInfo = $this->createEntityType();
    $this->bundleInfo = $this->createEntityBundle($this->entityTypeInfo['id']);
    // Start testing with a logged out user.
    $this->drupalLogout();
  }

  /**
   * Tests if the access to the default routes is properly checked.
   */
  public function testDefaultRoutes() {
    $routes = [
      'administer eck entity types' => [
        'eck.entity_type.list',
        'eck.entity_type.add',
        'entity.eck_entity_type.edit_form',
        'entity.eck_entity_type.delete_form',
      ],
      "create {$this->entityTypeInfo['id']} entities" => [
        'eck.entity.add_page',
        'eck.entity.add',
      ],
    ];
    $route_args = [
      'eck_entity_type' => $this->entityTypeInfo['id'],
      'eck_entity_bundle' => $this->bundleInfo['type'],
    ];
    foreach ($routes as $route_names) {
      foreach ($route_names as $route) {
        $this->drupalGet(Url::fromRoute($route, $route_args));
        $this->assertResponse(403, t('Anonymous users cannot access %route', ['%route' => $route]));
      }
    }

    \Drupal::entityTypeManager()->clearCachedDefinitions();
    foreach ($routes as $permission => $route_names) {
      $this->drupalLogin($this->drupalCreateUser([$permission]));
      foreach ($route_names as $route) {
        $this->drupalGet(Url::fromRoute($route, $route_args));
        $this->assertResponse(200, t('Users with the %perm permission can access %route', ['%route' => $route, '%perm' => $permission]));
      }
    }
  }

  /**
   * Tests if the access to dynamic routes is properly checked.
   */
  public function testDynamicRoutes() {
    $routes = [
      "view own {$this->entityTypeInfo['id']} entities" => [
        "eck.entity.{$this->entityTypeInfo['id']}.list",
      ],
      "view any {$this->entityTypeInfo['id']} entities" => [
        "eck.entity.{$this->entityTypeInfo['id']}.list",
      ],
      'bypass eck entity access' => [
        "eck.entity.{$this->entityTypeInfo['id']}.list",
      ],
      'administer eck entity bundles' => [
        "eck.entity.{$this->entityTypeInfo['id']}_type.list",
        "eck.entity.{$this->entityTypeInfo['id']}_type.add",
        "entity.{$this->entityTypeInfo['id']}_type.edit_form",
        "entity.{$this->entityTypeInfo['id']}_type.delete_form",
      ],
    ];
    $routeArguments = [
      "{$this->entityTypeInfo['id']}_type" => $this->bundleInfo['type'],
    ];

    foreach ($routes as $routeNames) {
      foreach ($routeNames as $routeName) {
        $this->drupalGet(Url::fromRoute($routeName, $routeArguments));
        $this->assertResponse(403, t('Anonymous users cannot access %route', ['%route' => $routeName]));
      }
    }

    \Drupal::entityTypeManager()->clearCachedDefinitions();
    foreach ($routes as $permission => $routeNames) {
      $this->drupalLogin($this->drupalCreateUser([$permission]));
      foreach ($routeNames as $routeName) {
        $this->drupalGet(Url::fromRoute($routeName, $routeArguments));
        $this->assertResponse(200, t('Users with the %perm permission can access %route', ['%route' => $routeName, '%perm' => $permission]));
      }
    }
  }

  /**
   * Tests if access handling for created entities is handled correctly.
   */
  public function testEntityAccess() {
    $entityTypeName = $this->entityTypeInfo['id'];
    $ownEntityPermissions = $anyEntityPermissions = ["create {$entityTypeName} entities"];
    foreach (['view', 'edit', 'delete'] as $op) {
      $ownEntityPermissions[] = "{$op} own {$entityTypeName} entities";
      $anyEntityPermissions[] = "{$op} any {$entityTypeName} entities";
    }
    $ownEntityUser = $this->drupalCreateUser($ownEntityPermissions);
    $anyEntityUser = $this->drupalCreateUser($anyEntityPermissions);

    $this->drupalLogin($anyEntityUser);
    $edit['title[0][value]'] = $this->randomMachineName();
    $route_args = [
      'eck_entity_type' => $entityTypeName,
      'eck_entity_bundle' =>  $this->bundleInfo['type'],
    ];
    $this->drupalPostForm(Url::fromRoute("eck.entity.add", $route_args), $edit, t('Save'));

    $this->drupalLogin($ownEntityUser);
    $edit['title[0][value]'] = $this->randomMachineName();
    $route_args = [
      'eck_entity_type' => $entityTypeName,
      'eck_entity_bundle' =>  $this->bundleInfo['type'],
    ];
    $this->drupalPostForm(Url::fromRoute("eck.entity.add", $route_args), $edit, t('Save'));

    // Get the entity that was created by the 'any' user.
    $arguments = [$entityTypeName => 1];
    $this->drupalGet(Url::fromRoute("entity.{$entityTypeName}.canonical", $arguments));
    $this->assertResponse(403, 'The user cannot see content which is not his own.');
    $this->drupalGet(Url::fromRoute("entity.{$entityTypeName}.edit_form", $arguments));
    $this->assertResponse(403, 'The user cannot edit content which is not his own.');
    $this->drupalGet(Url::fromRoute("entity.{$entityTypeName}.delete_form", $arguments));
    $this->assertResponse(403, 'The user cannot delete content which is not his own.');
    // Get the entity that was created by the 'own' user.
    $arguments = [$entityTypeName => 2];
    $this->drupalGet(Url::fromRoute("entity.{$entityTypeName}.canonical", $arguments));
    $this->assertResponse(200, 'The user can see content which is his own.');
    $this->drupalGet(Url::fromRoute("entity.{$entityTypeName}.edit_form", $arguments));
    $this->assertResponse(200, 'The user can edit content which is his own.');
    $this->drupalGet(Url::fromRoute("entity.{$entityTypeName}.delete_form", $arguments));
    $this->assertResponse(200, 'The user can delete content which not his own.');

    $this->drupalLogin($anyEntityUser);
    // Get the entity that was created by the 'any' user.
    $arguments = [$entityTypeName => 1];
    $this->drupalGet(Url::fromRoute("entity.{$entityTypeName}.canonical", $arguments));
    $this->assertResponse(200, 'The user can see content which is his own.');
    $this->drupalGet(Url::fromRoute("entity.{$entityTypeName}.edit_form", $arguments));
    $this->assertResponse(200, 'The user can edit content which is his own.');
    $this->drupalGet(Url::fromRoute("entity.{$entityTypeName}.delete_form", $arguments));
    $this->assertResponse(200, 'The user can delete content which is his own.');
    // Get the entity that was created by the 'own' user.
    $arguments = [$entityTypeName => 2];
    $this->drupalGet(Url::fromRoute("entity.{$entityTypeName}.canonical", $arguments));
    $this->assertResponse(200, 'The user can see content which is not his own.');
    $this->drupalGet(Url::fromRoute("entity.{$entityTypeName}.edit_form", $arguments));
    $this->assertResponse(200, 'The user can edit content which is not his own.');
    $this->drupalGet(Url::fromRoute("entity.{$entityTypeName}.delete_form", $arguments));
    $this->assertResponse(200, 'The user can delete content which is not his own.');
  }

}
