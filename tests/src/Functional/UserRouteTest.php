<?php

namespace Drupal\Tests\username_enumeration_prevention\Functional;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;

/**
 * Performs integration tests on UserRouteEventSubscriber.
 *
 * @group username_enumeration_prevention
 */
class UserRouteTest extends BrowserTestBase {

  /**
   * Asserts user routes returns 404 response code for anon users.
   */
  public function testCanonicalRoute() {
    $this->drupalGet(Url::fromRoute('entity.user.canonical'));
    $this->assertSession()->statusCodeEquals(404);

    $this->drupalGet(Url::fromRoute('user.cancel_confirm', [
      'user'  => '1',
      'timestamp' => 0,
      'hashed_pass' => 'foo',
    ]));
    $this->assertSession()->statusCodeEquals(404);
  }

}
