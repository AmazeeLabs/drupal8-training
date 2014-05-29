<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\source\d6\Drupal6SqlBaseTest.
 */

namespace Drupal\migrate_drupal\Tests\source\d6;

use Drupal\migrate\Tests\MigrateTestCase;

/**
 * @group migrate_drupal
 * @group Drupal
 */
class Drupal6SqlBaseTest extends MigrateTestCase {

  /**
   * Define bare minimum migration configuration.
   */
  protected $migrationConfiguration = array(
    'id' => 'Drupal6SqlBase',
  );

  /**
   * @var \Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase
   */
  protected $base;

  /**
   * Minimum database contents needed to test Drupal6SqlBase.
   */
  protected $databaseContents = array(
    'system' => array(
      array(
        'filename' => 'sites/all/modules/module1',
        'name' => 'module1',
        'type' => 'module',
        'status' => 1,
        'schema_version' => -1,
      ),
      array(
        'filename' => 'sites/all/modules/module2',
        'name' => 'module2',
        'type' => 'module',
        'status' => 0,
        'schema_version' => 7201,
      ),
      array(
        'filename' => 'sites/all/modules/test2',
        'name' => 'test2',
        'type' => 'theme',
        'status' => 1,
        'schema_version' => -1,
      ),
    ),
    'variable' => array(
      array(
        'name' => 'my_variable',
        'value' => 'b:1;',
      ),
    ),
  );

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'D6 SQL base class tests',
      'description' => 'Tests D6 SQL base class.',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $plugin = 'placeholder_id';
    $this->base = new TestDrupal6SqlBase($this->migrationConfiguration, $plugin, array(), $this->getMigration());
    $this->base->setDatabase($this->getDatabase($this->databaseContents));
  }

  /**
   * Tests for Drupal6SqlBase::getSystemData().
   */
  public function testGetSystemData() {
    $system_data = $this->base->getSystemData();
    // Should be 1 theme and 2 modules.
    $this->assertEquals(1, count($system_data['theme']));
    $this->assertEquals(2, count($system_data['module']));

    // Calling again should be identical.
    $this->assertSame($system_data, $this->base->getSystemData());
  }

  /**
   * Tests for Drupal6SqlBase::moduleExists().
   */
  public function testDrupal6ModuleExists() {
    // This module should exist.
    $this->assertTrue($this->base->moduleExistsWrapper('module1'));

    // These modules should not exist.
    $this->assertFalse($this->base->moduleExistsWrapper('module2'));
    $this->assertFalse($this->base->moduleExistsWrapper('module3'));
  }

  /**
   * Tests for Drupal6SqlBase::getModuleSchemaVersion().
   */
  public function testGetModuleSchemaVersion() {
    // Non-existent module.
    $this->assertFalse($this->base->getModuleSchemaVersionWrapper('module3'));

    // Disabled module should still return schema version.
    $this->assertEquals(7201, $this->base->getModuleSchemaVersionWrapper('module2'));

    // Enabled module.
    $this->assertEquals(-1, $this->base->getModuleSchemaVersionWrapper('module1'));
  }

  /**
   * Tests for Drupal6SqlBase::variableGet().
   */
  public function testVariableGet() {
    // Test default value.
    $this->assertEquals('my_default', $this->base->variableGetWrapper('non_existent_variable', 'my_default'));

    // Test non-default.
    $this->assertSame(TRUE, $this->base->variableGetWrapper('my_variable', FALSE));
  }
}

namespace Drupal\migrate_drupal\Tests\source\d6;

use Drupal\Core\Database\Connection;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;

/**
 * Extends the Drupal6SqlBase abstract class.
 */
class TestDrupal6SqlBase extends DrupalSqlBase {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array(
      'filename' => t('The path of the primary file for this item.'),
      'name' => t('The name of the item; e.g. node.'),
      'type' => t('The type of the item, either module, theme, or theme_engine.'),
      'owner' => t("A theme's 'parent'. Can be either a theme or an engine."),
      'status' => t('Boolean indicating whether or not this item is enabled.'),
      'throttle' => t('Boolean indicating whether this item is disabled when the throttle.module disables throttleable items.'),
      'bootstrap' => t('Boolean indicating whether this module is loaded during Drupal\'s early bootstrapping phase (e.g. even before the page cache is consulted).'),
      'schema_version' => t('The module\'s database schema version number.'),
      'weight' => t('The order in which this module\'s hooks should be invoked.'),
      'info' => t('A serialized array containing information from the module\'s .info file.'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->database
      ->select('system', 's')
      ->fields('s', array('filename', 'name', 'schema_version'));
    return $query;
  }

  /**
   * Tweaks Drupal6SqlBase to set a new database connection for tests.
   *
   * @param \Drupal\Core\Database\Connection
   *   The new conection to use.
   *
   * @see \Drupal\migrate\Tests\MigrateSqlTestCase
   */
  public function setDatabase(Connection $database) {
    $this->database = $database;
  }

  /**
   * Tweaks Drupal6SqlBase to set a new module handler for tests.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface
   *   The new module handler to use.
   *
   * @see \Drupal\migrate\Tests\MigrateSqlTestCase
   */
  public function setModuleHandler(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }

  /**
   * Wrapper method to test protected method moduleExists().
   */
  public function moduleExistsWrapper($module) {
    return parent::moduleExists($module);
  }

  /**
   * Wrapper method to test protected method getModuleSchemaVersion().
   */
  public function getModuleSchemaVersionWrapper($module) {
    return parent::getModuleSchemaVersion($module);
  }

  /**
   * Wrapper method to test protected method variableGet().
   */
  public function variableGetWrapper($name, $default) {
    return parent::variableGet($name, $default);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return array();
  }
}