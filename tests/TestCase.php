<?php
/**
 * Big Pit Tests: Base Test Class
 *
 * @package wp-big-pit
 */

namespace Alley\WP\Big_Pit\Tests;

use Alley\WP\Big_Pit;
use Mantle\Testkit\Test_Case as TestkitTest_Case;

/**
 * Big Pit Base Test Case
 */
abstract class TestCase extends TestkitTest_Case {
	/**
	 * Create the database table.
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		Big_Pit::instance()->boot();
	}

	/**
	 * Drop the database table.
	 */
	public static function tearDownAfterClass(): void {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->query( 'DROP TABLE ' . $wpdb->big_pit );
		delete_option( 'wp_big_pit_database_version' );

		parent::tearDownAfterClass();
	}
}
