<?php
/**
 * Big Pit Tests: Base Test Class
 *
 * @package wp-big-pit
 */

namespace Alley\WP\Big_Pit\Tests;

use Alley\WP\Big_Pit;
use Alley\WP\Big_Pit\Client;
use Mantle\Testkit\Test_Case as TestkitTest_Case;

/**
 * Big Pit Base Test Case
 */
abstract class TestCase extends TestkitTest_Case {
	/**
	 * Drop the database table.
	 */
	public function tearDown(): void {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->query( 'DROP TABLE ' . $wpdb->big_pit );
		delete_option( 'wp_big_pit_database_version' );

		parent::tearDown();
	}

	/**
	 * Test CRUD operations.
	 *
	 * @param Client $client Client to test.
	 */
	public function crud_assertions( Client $client ): void {
		$key1 = rand_str();
		$key2 = rand_str();
		$key3 = rand_str();
		$val1 = rand_str();
		$val2 = rand_str();
		$val3 = rand_str();
		$grp1 = rand_str();
		$grp2 = rand_str();

		$client->set( $key1, $val1, $grp1 );
		$client->set( $key2, $val2, $grp1 );
		$client->set( $key3, $val3, $grp2 );

		// Get key1, delete it, and assert it's gone.
		$this->assertSame( $val1, $client->get( $key1, $grp1 ) );
		$client->delete( $key1, $grp1 );
		$this->assertNull( $client->get( $key1, $grp1 ) );

		// Get key2, flush group1, and assert it's gone.
		$this->assertSame( $val2, $client->get( $key2, $grp1 ) );
		$client->flush_group( $grp1 );
		$this->assertNull( $client->get( $key2, $grp1 ) );

		// key3 should still be there.
		$this->assertSame( $val3, $client->get( $key3, $grp2 ) );
	}
}
