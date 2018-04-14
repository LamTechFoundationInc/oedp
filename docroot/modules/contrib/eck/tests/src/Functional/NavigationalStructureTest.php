<?php

namespace Drupal\Tests\eck\Functional;

use Drupal\Core\Url;
use Drupal\eck\Entity\EckEntityType;
use Drupal\Tests\BrowserTestBase;

/**
 * Test ECK's navigational structure. This includes routing, paths, breadcrumbs
 * and page titles.
 *
 * @group ECK
 */
class NavigationalStructureTest extends BrowserTestBase {

  public static $modules = ['eck', 'block'];
  private $baseCrumbs = [
    'Home',
    'Administration',
    'Structure'
  ];

  /** @var string */
  private $entityTypeMachineName;
  /** @var string */
  private $entityTypeLabel;
  /** @var string */
  private $entityBundleMachineName;
  /** @var string */
  private $entityBundleLabel;

  public function setUp() {
    parent::setUp();
    $user = $this->drupalCreateUser([
      'administer eck entities',
      'administer eck entity bundles',
      'administer eck entity types',
      'bypass eck entity access',
      'access administration pages',
    ]);
    $this->drupalLogin($user);

    $this->entityTypeMachineName = strtolower($this->randomMachineName());
    $this->entityTypeLabel = strtolower($this->randomMachineName());
    $this->createEntityType($this->entityTypeMachineName, $this->entityTypeLabel);

    $this->entityBundleMachineName = strtolower($this->randomMachineName());
    $this->entityBundleLabel = strtolower($this->entityBundleMachineName);
    $this->createEntityBundle($this->entityTypeMachineName, $this->entityBundleMachineName, $this->entityBundleLabel);

    $this->placeBlock('system_breadcrumb_block');
    $this->placeBlock('page_title_block');
  }

  /**
   * @param $entityTypeId
   * @param $entityTypeLabel
   */
  protected function createEntityType($entityTypeId, $entityTypeLabel) {
    $entityType = EckEntityType::create([
      'id' => $entityTypeId,
      'label' => $entityTypeLabel
    ]);
    $entityType->save();
  }

  /**
   * @param $entityTypeId
   * @param $entityBundleMachineName
   * @param $entityBundleName
   */
  protected function createEntityBundle($entityTypeId, $entityBundleMachineName, $entityBundleName) {
    $entityBundle = \Drupal::entityTypeManager()
      ->getStorage($entityTypeId . '_type')
      ->create([
        'type' => $entityBundleMachineName,
        'name' => $entityBundleName
      ]);
    $entityBundle->save();
  }

  /**
   * @return \Drupal\Core\Entity\EntityStorageInterface
   */
  private function getEntityStorageHandler() {
    return \Drupal::entityTypeManager()
      ->getStorage($this->entityTypeMachineName);
  }

  /**
   * @param $route
   * @param $routeArguments
   * @param $expectedUrl
   * @param $expectedTitle
   * @param $crumbs
   */
  private function assertCorrectPageOnRoute($route, $routeArguments, $expectedUrl, $expectedTitle, $crumbs = []) {
    $url = Url::fromRoute($route, $routeArguments);

    self::assertEquals($expectedUrl, $url->getInternalPath());
    $this->drupalGet($url);
    $this->assertTitleEquals($expectedTitle);
    $this->assertBreadcrumbsVisible(array_merge($this->baseCrumbs, $crumbs));
  }

  /**
   * @param string $expectedTitle
   */
  private function assertTitleEquals($expectedTitle) {
    $titleElement = $this->getSession()
      ->getPage()
      ->find('css', '.page-title');
    $this->assertEquals($expectedTitle, $titleElement->getText());
  }

  /**
   * @param string[] $expectedBreadcrumbs
   */
  private function assertBreadcrumbsVisible(array $expectedBreadcrumbs) {
    $breadcrumbs = $this->getSession()
      ->getPage()
      ->findAll('css', '.breadcrumb a');
    self::assertEquals(count($expectedBreadcrumbs), count($breadcrumbs));
    do {
      $expectedCrumb = array_shift($expectedBreadcrumbs);
      $actualCrumb = array_shift($breadcrumbs)->getText();
      self::assertEquals($expectedCrumb, $actualCrumb);
    } while (!empty($expectedBreadcrumbs));
  }

  /**
   * @test
   */
  public function entityTypeList() {
    $route = 'eck.entity_type.list';
    $routeArguments = [];
    $expectedUrl = 'admin/structure/eck/entity_type';
    $expectedTitle = 'ECK Entity Types';

    $this->assertCorrectPageOnRoute($route, $routeArguments, $expectedUrl, $expectedTitle);
  }

  /**
   * @test
   */
  public function entityTypeAdd() {
    $routeArguments = [];
    $route = 'eck.entity_type.add';
    $expectedUrl = 'admin/structure/eck/entity_type/add';
    $expectedTitle = 'Add entity type';
    $crumbs = ['ECK Entity Types'];

    $this->assertCorrectPageOnRoute($route, $routeArguments, $expectedUrl, $expectedTitle, $crumbs);
  }

  /**
   * @test
   */
  public function entityTypeEdit() {
    $route = 'entity.eck_entity_type.edit_form';
    $routeArguments = ['eck_entity_type' => $this->entityTypeMachineName];
    $expectedUrl = 'admin/structure/eck/entity_type/manage/' . $this->entityTypeMachineName;
    $expectedTitle = 'Edit entity type';
    $crumbs = ['ECK Entity Types'];

    $this->assertCorrectPageOnRoute($route, $routeArguments, $expectedUrl, $expectedTitle, $crumbs);
  }

  /**
   * @test
   */
  public function entityTypeDelete() {
    $route = 'entity.eck_entity_type.delete_form';
    $routeArguments = ['eck_entity_type' => $this->entityTypeMachineName];
    $expectedUrl = 'admin/structure/eck/entity_type/manage/' . $this->entityTypeMachineName . '/delete';
    $expectedTitle = 'Delete entity type';
    $crumbs = [
      'ECK Entity Types',
      "Edit entity type"
    ];

    $this->assertCorrectPageOnRoute($route, $routeArguments, $expectedUrl, $expectedTitle, $crumbs);
  }

  /**
   * @test
   */
  public function entityList() {
    $route = "eck.entity.{$this->entityTypeMachineName}.list";
    $routeArguments = [];
    $expectedUrl = 'admin/structure/eck/entity/' . $this->entityTypeMachineName;
    $expectedTitle = ucfirst("{$this->entityTypeLabel} content");

    $this->assertCorrectPageOnRoute($route, $routeArguments, $expectedUrl, $expectedTitle);
  }

  /**
   * @test
   */
  public function entityAddPage() {
    $route = 'eck.entity.add_page';
    $routeArguments = ['eck_entity_type' => $this->entityTypeMachineName];
    $expectedUrl = 'admin/structure/eck/' . $this->entityTypeMachineName . '/content/add';
    $expectedTitle = "Add " . $this->entityTypeLabel . " content";

    $this->assertCorrectPageOnRoute($route, $routeArguments, $expectedUrl, $expectedTitle);
  }

  /**
   * @test
   */
  public function entityAdd() {
    $route = 'eck.entity.add';
    $routeArguments = [
      'eck_entity_type' => $this->entityTypeMachineName,
      'eck_entity_bundle' => $this->entityBundleMachineName
    ];
    $expectedUrl = 'admin/structure/eck/' . $this->entityTypeMachineName . '/add/' . $this->entityBundleMachineName;
    $expectedTitle = "Add {$this->entityBundleMachineName} content";

    $this->assertCorrectPageOnRoute($route, $routeArguments, $expectedUrl, $expectedTitle);
  }

  /**
   * @test
   */
  public function entityEdit() {
    $entity = $this->getEntityStorageHandler()
      ->create(['type' => $this->entityBundleMachineName]);
    $entity->save();

    $route = "entity.{$this->entityTypeMachineName}.edit_form";
    $routeArguments = [$this->entityTypeMachineName => $entity->id()];
    $expectedUrl = 'admin/structure/eck/entity/' . $this->entityTypeMachineName . '/' . $entity->id() . '/edit';
    $expectedTitle = "Edit{$this->entityTypeLabel}";
    $crumbs = [
      ucfirst("{$this->entityTypeLabel} content"),
      $this->entityTypeLabel,
    ];

    $this->assertCorrectPageOnRoute($route, $routeArguments, $expectedUrl, $expectedTitle, $crumbs);
  }

  /**
   * @test
   */
  public function entityView() {
    $entity = $this->getEntityStorageHandler()
      ->create(['type' => $this->entityBundleMachineName]);
    $entity->save();

    $route = "entity.{$this->entityTypeMachineName}.canonical";
    $routeArguments = [$this->entityTypeMachineName => $entity->id()];
    $expectedUrl = 'admin/structure/eck/entity/' . $this->entityTypeMachineName . '/' . $entity->id();
    $expectedTitle = "$this->entityTypeLabel";
    $crumbs = [
      ucfirst("{$this->entityTypeLabel} content"),
    ];

    $this->assertCorrectPageOnRoute($route, $routeArguments, $expectedUrl, $expectedTitle, $crumbs);
  }

  /**
   * @test
   */
  public function entityDelete() {
    $entity = $this->getEntityStorageHandler()
      ->create(['type' => $this->entityBundleMachineName]);
    $entity->save();

    $route = "entity.{$this->entityTypeMachineName}.delete_form";
    $routeArguments = [$this->entityTypeMachineName => $entity->id()];
    $expectedUrl = 'admin/structure/eck/entity/' . $this->entityTypeMachineName . '/' . $entity->id() . '/delete';
    $expectedTitle = "Are you sure you want to delete entity ?";
    $crumbs = [
      ucfirst("{$this->entityTypeLabel} content"),
      $this->entityTypeLabel,
    ];

    $this->assertCorrectPageOnRoute($route, $routeArguments, $expectedUrl, $expectedTitle, $crumbs);
  }

  /**
   * @test
   */
  public function entityBundleList() {
    $route = "eck.entity.{$this->entityTypeMachineName}_type.list";
    $routeArguments = [];
    $expectedUrl = 'admin/structure/eck/entity/' . $this->entityTypeMachineName . '/types';
    $expectedTitle = ucfirst("{$this->entityTypeLabel} bundles");
    $crumbs = [ucfirst("{$this->entityTypeLabel} content")];

    $this->assertCorrectPageOnRoute($route, $routeArguments, $expectedUrl, $expectedTitle, $crumbs);
  }

  /**
   * @test
   */
  public function entityBundleAdd() {
    $route = "eck.entity.{$this->entityTypeMachineName}_type.add";
    $routeArguments = [];
    $expectedUrl = 'admin/structure/eck/entity/' . $this->entityTypeMachineName . '/types/add';
    $expectedTitle = "Add {$this->entityTypeLabel} bundle";
    $crumbs = [
      ucfirst("{$this->entityTypeLabel} content"),
      ucfirst("{$this->entityTypeLabel} bundles"),
    ];

    $this->assertCorrectPageOnRoute($route, $routeArguments, $expectedUrl, $expectedTitle, $crumbs);
  }

  /**
   * @test
   */
  public function entityBundleEdit() {
    $route = "entity.{$this->entityTypeMachineName}_type.edit_form";
    $routeArguments = ["{$this->entityTypeMachineName}_type" => $this->entityBundleMachineName];
    $expectedUrl = 'admin/structure/eck/entity/' . $this->entityTypeMachineName . '/types/manage/' . $this->entityBundleMachineName;
    $expectedTitle = "Edit {$this->entityTypeLabel} bundle";
    $crumbs = [
      ucfirst("{$this->entityTypeLabel} content"),
      ucfirst("{$this->entityTypeLabel} bundles"),
    ];

    $this->assertCorrectPageOnRoute($route, $routeArguments, $expectedUrl, $expectedTitle, $crumbs);
  }

  /**
   * @test
   */
  public function entityBundleDelete() {
    $route = "entity.{$this->entityTypeMachineName}_type.delete_form";
    $routeArguments = ["{$this->entityTypeMachineName}_type" => $this->entityBundleMachineName];
    $expectedUrl = 'admin/structure/eck/entity/' . $this->entityTypeMachineName . '/types/manage/' . $this->entityBundleMachineName . '/delete';
    $expectedTitle = "Are you sure you want to delete the entity bundle {$this->entityBundleLabel}?";
    $crumbs = [
      ucfirst("{$this->entityTypeLabel} content"),
      ucfirst("{$this->entityTypeLabel} bundles"),
      "Edit {$this->entityTypeLabel} bundle",
    ];

    $this->assertCorrectPageOnRoute($route, $routeArguments, $expectedUrl, $expectedTitle, $crumbs);
  }
}
