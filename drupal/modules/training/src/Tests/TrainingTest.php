<?php
/**
 * @file
 * Test case for testing the training module.
 *
 * This file contains the test cases to check if module is performing as
 * expected.
 */

namespace Drupal\training\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests existence upcoming movies page.
 *
 * @group aggregator
 */
class TrainingTest extends WebTestBase {

  public static $modules = array('training');

  protected $webUser;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Training functionality',
      'description' => 'Tests to ensure the upcoming movies page is visible.',
      'group' => 'Training',
    );
  }


  /**
   * Main test.
   *
   * Login user, create an example node, and test page functionality through
   * the admin and user interfaces.
   */
  public function testTrainingBasic() {
    // Create a user with permissions to access 'simple' page and login.
    $this->webUser = $this->drupalCreateUser();
    $this->drupalLogin($this->webUser);

    // Verify that user can access upcoming movie content.
    $this->drupalGet('upcoming-movies');
    $this->assertResponse(200, 'Upcoming movies successfully accessed.');

  }

}
