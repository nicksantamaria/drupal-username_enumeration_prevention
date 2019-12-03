<?php

namespace Drupal\Tests\username_enumeration_prevention\Functional;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;

/**
 * Performs integration tests on forms.
 *
 * @group username_enumeration_prevention
 */
class UserFormTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['username_enumeration_prevention'];

  /**
   * Asserts messages on various anonymous forms dont include usernames.
   */
  public function testUserResetSpoof() {
    $account = $this->createUser();
    $this->drupalGet(Url::fromRoute('user.reset.login', [
      'uid'  => $account->id(),
      'timestamp' => 1558364581,
      'hash' => 'GMGVHMkyV0I-1XnefRMKrt5gBa2qVq4oOdjLtqCCBqM',
    ]));
    $this->assertSession()->pageTextNotContains($account->getAccountName());
  }

}
