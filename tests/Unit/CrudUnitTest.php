<?php
/**
 * Big Pit Tests: CrudUnitTest class file
 *
 * @package wp-big-pit
 */

namespace Alley\WP\Big_Pit\Tests\Unit;

use Alley\WP\Big_Pit;
use Alley\WP\Big_Pit\Tests\TestCase;

/**
 * CRUD tests.
 */
class CrudUnitTest extends TestCase {
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
}
