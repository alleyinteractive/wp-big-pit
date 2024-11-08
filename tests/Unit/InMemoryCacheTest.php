<?php
/**
 * Big Pit Tests: InMemoryCacheTest class file
 *
 * @package wp-big-pit
 */

namespace Alley\WP\Big_Pit\Tests\Unit;

use Alley\WP\Big_Pit;
use Alley\WP\Big_Pit\Tests\TestCase;

/**
 * Test in-memory cache.
 */
class InMemoryCacheTest extends TestCase {
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

	/**
	 * Check for improper reuse of a cached object.
	 */
	public function test_cached_object_reference() {
		$big_pit = Big_Pit::instance();
		$big_pit->set( 'key1', (object) [ 'foo' => 'bar' ], 'group1' );
		$this->assertNotSame( $big_pit->get( 'key1', 'group1' ), $big_pit->get( 'key1', 'group1' ) );
	}
}
