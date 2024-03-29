<?php

namespace Drupal\Tests\username_enumeration_prevention\Functional;

use Drupal\Core\Test\AssertMailTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Tests\system\Functional\Cache\PageCacheTagsTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * Ensure flood protection works, despite lack of end-user feedback.
 *
 * @group username_enumeration_prevention
 */
class FloodTest extends PageCacheTagsTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  use AssertMailTrait {
    getMails as drupalGetMails;
  }

  use UserCreationTrait;

  use StringTranslationTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['username_enumeration_prevention'];

  /**
   * Tests password reset flood control for one IP.
   */
  public function testUserResetPasswordIpFloodControl() {
    \Drupal::configFactory()->getEditable('user.flood')
      ->set('ip_limit', 3)
      ->save();

    $name = 'foo';
    $this->createUser([], $name, FALSE, ['mail' => 'foo@bar']);

    // Try 3 requests that should not trigger flood control.
    for ($i = 0; $i < 3; $i++) {
      $this->drupalGet('user/password');
      $edit = ['name' => $name];
      $this->submitForm($edit, $this->t('Submit'));
    }

    // The next request should trigger flood control.
    $this->drupalGet('user/password');
    $edit = ['name' => $this->randomMachineName()];
    $this->submitForm($edit, $this->t('Submit'));

    // Error should not be displayed to the end user.
    $this->assertSession()->pageTextNotContains($this->t('Too many password recovery requests from your IP address. It is temporarily blocked. Try again later or contact the site administrator.'));

    // But mail should be.
    $mail = $this->drupalGetMails();
    $this->assertTrue(!empty($mail), "password reset mails were sent");
  }

}
