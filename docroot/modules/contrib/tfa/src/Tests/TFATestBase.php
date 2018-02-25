<?php
namespace Drupal\tfa\Tests;

use Drupal\simpletest\WebTestBase;
use Otp\GoogleAuthenticator;
use Otp\Otp;

class TFATestBase  extends WebTestBase {
  /**
   * Object containing the external validation library.
   *
   * @var GoogleAuthenticator
   */
  protected $auth;

  /**
   * Test key for encryption
   *
   * @var \Drupal\key\Entity\Key
   */
  protected $testKey;

  public static $modules = ['tfa', 'node', 'encrypt', 'encrypt_test', 'key', 'ga_login'];

  public function setUp() {
    parent::setUp();

    // OTP class to do GA Login validation.
    $this->auth = new \StdClass();
    $this->auth->otp = new Otp();
    $this->auth->ga  = new GoogleAuthenticator();
    $this->generateRoleKey();
    $this->generateEncryptionProfile();
  }

  /**
   * Generate a Role key.
   */
  public function generateRoleKey() {
    // Generate a key; at this stage the key hasn't been configured completely.
    $values = [
      'id' => 'testing_key_128',
      'label' => 'Testing Key 128 bit',
      'key_type' => "encryption",
      'key_type_settings' => ['key_size' => '128'],
      'key_provider' => 'config',
      'key_input' => 'none',
      // This is actually 16bytes but oh well..
      'key_provider_settings' => ['key_value' => 'mustbesixteenbit'],
    ];
    \Drupal::entityTypeManager()
      ->getStorage('key')
      ->create($values)
      ->save();
  }

  /**
   * Generate an Encryption profile for a Role key.
   */
  public function generateEncryptionProfile() {
    $values = [
      'id' => 'test_encryption_profile',
      'label' => 'Test encryption profile',
      'encryption_method' => 'test_encryption_method',
      'encryption_key' => 'testing_key_128'
    ];

    \Drupal::entityTypeManager()
      ->getStorage('encryption_profile')
      ->create($values)
      ->save();
  }

}