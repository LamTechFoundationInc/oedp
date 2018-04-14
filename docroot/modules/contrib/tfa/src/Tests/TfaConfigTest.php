<?php

namespace Drupal\tfa\Tests;


/**
 * Tests the Tfa UI.
 *
 * @group Tfa
 */
class TfaConfigTest extends TFATestBase {
  /**
   * User doing the TFA Validation.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $webUser;

  /**
   * Administrator to handle configurations.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    // Enable TFA module and the test module.
    parent::setUp();
    $this->webUser = $this->drupalCreateUser(['setup own tfa']);
    $this->adminUser = $this->drupalCreateUser(['administer users', 'administer site configuration']);
  }

  /**
   * Test to check if configurations are working as desired.
   */
  public function testTfaConfig() {
    // Check that config form is restricted for users.
    $this->drupalLogin($this->webUser);
    $this->drupalGet('admin/config/people/tfa');
    $this->assertResponse(403);

    // Check that config form is accessible to admins.
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/config/people/tfa');
    $this->assertResponse(200);
    $this->assertText($this->uiStrings('config-form'));

    $edit = [
      'tfa_enabled' => TRUE,
      'tfa_validate' => 'tfa_hotp',
      'tfa_login[tfa_trusted_browser]' => 'tfa_trusted_browser',
    ];

    $this->drupalPostForm(NULL, $edit, t('Save configuration'));
    $this->assertText($this->uiStrings('config-saved'));
    $this->assertOptionSelected('edit-tfa-validate', 'tfa_hotp', t('Plugin selected'));
    $this->assertFieldChecked('edit-tfa-fallback-tfa-hotp-tfa-recovery-code-enable', t('Fallback selected'));
  }

  /**
   * TFA module user interface strings.
   *
   * @param string $id
   *   ID of string.
   *
   * @return string
   *   UI message for corresponding id.
   */
  protected function uiStrings($id) {
    switch ($id) {
      case 'config-form':
        return 'TFA Settings';

      case 'config-saved':
        return 'The configuration options have been saved.';
    }
  }
}
