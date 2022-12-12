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
   * {@inheritdoc}
   */
  protected static $modules = ['user', 'shortcut'];

  /**
   * Tests user routes.
   *
   * @param string $routeId
   *   Route ID.
   * @param array $routeParameters
   *   Additional route parameters.
   * @param array $modules
   *   Enable these modules.
   * @param int $expectedStatus
   *   Expected HTTP status.
   *
   * @dataProvider providerTestUserRoutes
   */
  public function testUserRoutes(string $routeId, array $routeParameters, array $modules, int $expectedStatus) {
    $user = $this->drupalCreateUser();
    \Drupal::service('module_installer')->install($modules);
    $routeParameters['user'] = $user->id();
    $this->drupalGet(Url::fromRoute($routeId, $routeParameters));
    $this->assertSession()->statusCodeEquals($expectedStatus);
  }

  /**
   * Provides routes for testing.
   */
  public function providerTestUserRoutes(): array {
    // user.module.
    $scenarios['user canonical'] = ['entity.user.canonical'];
    $scenarios['user edit form'] = ['entity.user.edit_form'];
    $scenarios['user cancel form'] = ['entity.user.cancel_form'];
    $scenarios['cancel confirm 1'] = ['user.cancel_confirm'];
    $scenarios['cancel confirm 2'] = [
      'user.cancel_confirm',
      ['timestamp' => 0, 'hashed_pass' => 'foo'],
    ];

    // contact.module.
    $scenarios['contact user contact form'] = [
      'entity.user.contact_form',
      [],
      ['contact'],
    ];

    // shortcut.module.
    $scenarios['user shortcut list'] = ['shortcut.set_switch'];

    $data = [];
    foreach ($scenarios as $key => $scenario) {
      $scenario = $scenario + array_fill_keys(range(1, 2), []);
      // Test Drupal user routes return 403 out of the box. We use these to test
      // if core changed the behaviour of these routes, and now 404.
      $data['without uep ' . $key] = $scenario + [3 => 403];
      $scenario[2][] = 'username_enumeration_prevention';
      $data['with uep ' . $key] = $scenario + [3 => 404];
    }

    return $data;
  }

}
