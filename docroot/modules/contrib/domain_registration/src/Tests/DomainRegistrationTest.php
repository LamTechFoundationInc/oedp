<?php
/**
 * @file
 * Drupal\domain_registration\Tests\DomainRegistrationTest.
 */

namespace Drupal\domain_registration\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Test if the Domain Registration module properly allows and denies specified domains.
 *
 * @group domain_registration
 */
class DomainRegistrationTest extends WebTestBase {

  public static $modules = array('domain_registration');

  public function testAllowExactMatch() {
    \Drupal::configFactory()->getEditable('domain_registration.settings')->set('method', DOMAIN_REGISTRATION_ALLOW)->save();
    \Drupal::configFactory()->getEditable('domain_registration.settings')->set('pattern', 'example.com')->save();

    // Get the user data for registration.
    $edit = array(
      'name' => $this->randomMachineName(),
      'mail' => $this->randomMachineName() . '@example.com',
    );
    // Attempt to register a user with a whitelist email.
    $this->drupalPostForm('user/register', $edit, t('Create new account'));
    $this->assertText(t('A welcome message'), t('Thank you for applying for an account'));
  }

  public function testAllowEmptyPatternMatch() {
    \Drupal::configFactory()->getEditable('domain_registration.settings')->set('method', DOMAIN_REGISTRATION_ALLOW)->save();
    \Drupal::configFactory()->getEditable('domain_registration.settings')->set('pattern', '')->save();

    // Get the user data for registration.
    $edit = array(
      'name' => $this->randomMachineName(),
      'mail' => $this->randomMachineName() . '@example.com',
    );
    // Attempt to register a user with a whitelist email.
    $this->drupalPostForm('user/register', $edit, t('Create new account'));
    $this->assertText(t('A welcome message'), t('Thank you for applying for an account'));
  }

  public function testAllowWildcardMatch() {
    \Drupal::configFactory()->getEditable('domain_registration.settings')->set('method', DOMAIN_REGISTRATION_ALLOW)->save();
    \Drupal::configFactory()->getEditable('domain_registration.settings')->set('pattern', '*.example.com')->save();

    // Get the user data for registration.
    $edit = array(
      'name' => $this->randomMachineName(),
      'mail' => $this->randomMachineName() . '@subdomain.example.com',
    );
    // Attempt to register a user with a whitelist email.
    $this->drupalPostForm('user/register', $edit, t('Create new account'));
    $this->assertText(t('A welcome message'), t('Thank you for applying for an account'));
  }

  public function testDenyNoMatch() {
    \Drupal::configFactory()->getEditable('domain_registration.settings')->set('method', DOMAIN_REGISTRATION_ALLOW)->save();
    \Drupal::configFactory()->getEditable('domain_registration.settings')->set('pattern', 'example.com')->save();

    // Get the user data for registration.
    $edit = array(
      'name' => $this->randomMachineName(),
      'mail' => $this->randomMachineName() . '@otherexample.com',
    );
    // Attempt to register a user with a non whitelist email.
    $this->drupalPostForm('user/register', $edit, t('Create new account'));
    $this->assertText(t('You are not allowed to register for this site.'), t('User was successfully denied registration.'));
  }

  public function testDenyExactMatch() {
    \Drupal::configFactory()->getEditable('domain_registration.settings')->set('method', DOMAIN_REGISTRATION_DENY)->save();
    \Drupal::configFactory()->getEditable('domain_registration.settings')->set('pattern', 'example.com')->save();

    // Get the user data for registration.
    $edit = array(
      'name' => $this->randomMachineName(),
      'mail' => $this->randomMachineName() . '@example.com',
    );
    // Attempt to register a user with a blacklist email.
    $this->drupalPostForm('user/register', $edit, t('Create new account'));
    $this->assertText(t('You are not allowed to register for this site.'), t('User was successfully denied registration.'));
  }

  public function testDenyWildcardMatch() {
    \Drupal::configFactory()->getEditable('domain_registration.settings')->set('method', DOMAIN_REGISTRATION_DENY)->save();
    \Drupal::configFactory()->getEditable('domain_registration.settings')->set('pattern', '*.example.com')->save();

    // Get the user data for registration.
    $edit = array(
      'name' => $this->randomMachineName(),
      'mail' => $this->randomMachineName() . '@subdomain.example.com',
    );
    // Attempt to register a user with blacklist email.
    $this->drupalPostForm('user/register', $edit, t('Create new account'));
    $this->assertText(t('You are not allowed to register for this site.'), t('User was successfully denied registration.'));
  }

  public function testDenyCustomMessage() {
    \Drupal::configFactory()->getEditable('domain_registration.settings')->set('method', DOMAIN_REGISTRATION_DENY)->save();
    \Drupal::configFactory()->getEditable('domain_registration.settings')->set('message', 'foo bar baz')->save();
    \Drupal::configFactory()->getEditable('domain_registration.settings')->set('pattern', 'example.com')->save();

    // Get the user data for registration.
    $edit = array(
      'name' => $this->randomMachineName(),
      'mail' => $this->randomMachineName() . '@example.com',
    );
    // Attempt to register a user with a blacklist email.
    $this->drupalPostForm('user/register', $edit, t('Create new account'));
    $this->assertText(t('foo bar baz'), t('Custom denial message was shown.'));
  }

  public function testAllowNoMatch() {
    \Drupal::configFactory()->getEditable('domain_registration.settings')->set('method', DOMAIN_REGISTRATION_DENY)->save();
    \Drupal::configFactory()->getEditable('domain_registration.settings')->set('pattern', 'example.com')->save();

    // Get the user data for registration.
    $edit = array(
      'name' => $this->randomMachineName(),
      'mail' => $this->randomMachineName() . '@otherexample.com',
    );
    // Attempt to register a user with a non blacklist email.
    $this->drupalPostForm('user/register', $edit, t('Create new account'));
    $this->assertText(t('A welcome message'), t('Thank you for applying for an account'));
  }
}
