<?php
/**
 * Big Pit Tests: CrudUnitTest class file
 *
 * @package wp-big-pit
 */

namespace Alley\WP\Big_Pit\Tests\Unit;

use Alley\WP\Big_Pit;
use PHPUnit\Framework\TestCase;

/**
 * CRUD tests.
 */
class CrudUnitTest extends TestCase {
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

	/**
	 * Test CRUD operations.
	 */
	public function test_crud() {
		$big_pit = Big_Pit::instance();

		$key1 = 'key1';
		$key2 = 'key2';
		$key3 = 'key3';
		$val1 = 'value1';
		$val2 = 'value2';
		$val3 = 'value3';
		$grp1 = 'group1';
		$grp2 = 'group2';

		$big_pit->set( $key1, $val1, $grp1 );
		$big_pit->set( $key2, $val2, $grp1 );
		$big_pit->set( $key3, $val3, $grp2 );

		// Get key1, delete it, and assert it's gone.
		$this->assertSame( $val1, $big_pit->get( $key1, $grp1 ) );
		$big_pit->delete( $key1, $grp1 );
		$this->assertNull( $big_pit->get( $key1, $grp1 ) );

		// Get key2, flush group1, and assert it's gone.
		$this->assertSame( $val2, $big_pit->get( $key2, $grp1 ) );
		$big_pit->flush_group( $grp1 );
		$this->assertNull( $big_pit->get( $key2, $grp1 ) );

		// key3 should still be there.
		$this->assertSame( $val3, $big_pit->get( $key3, $grp2 ) );
	}

	/**
	 * Test for expected number of queries with the in-memory cache.
	 */
	public function test_in_memory_cache() {
		global $wpdb;

		$big_pit = Big_Pit::instance();

		// Two fetches of the same key should only result in one query.
		$num_queries_before = $wpdb->num_queries;
		$big_pit->get( 'key1', 'group1' );
		$big_pit->get( 'key1', 'group1' );
		$this->assertSame( 1, $wpdb->num_queries - $num_queries_before );

		$big_pit->set( 'key1', 'value1', 'group1' );

		// Value has changed, so there should be another query.
		$num_queries_before = $wpdb->num_queries;
		$big_pit->get( 'key1', 'group1' );
		$this->assertSame( 1, $wpdb->num_queries - $num_queries_before );

		// Fetching it again should not result in another query.
		$big_pit->get( 'key1', 'group1' );
		$this->assertSame( 1, $wpdb->num_queries - $num_queries_before );

		$big_pit->delete( 'key1', 'group1' );

		// Value has changed, so there should be another query.
		$num_queries_before = $wpdb->num_queries;
		$big_pit->get( 'key1', 'group1' );
		$this->assertSame( 1, $wpdb->num_queries - $num_queries_before );

		$big_pit->set( 'key1', 'value1', 'group1' );
		$big_pit->set( 'key2', 'value2', 'group1' );
		$big_pit->get( 'key1', 'group1' );
		$big_pit->get( 'key2', 'group1' );
		$big_pit->flush_group( 'group1' );

		// All values have changed, so there should be queries for each value in the group that was set.
		$num_queries_before = $wpdb->num_queries;
		$big_pit->get( 'key1', 'group1' );
		$big_pit->get( 'key2', 'group1' );
		$this->assertSame( 2, $wpdb->num_queries - $num_queries_before );
	}
}
