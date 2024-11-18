<?php
/**
 * Big Pit Tests: Big Pit Feature Test
 *
 * @package wp-big-pit
 */

namespace Alley\WP\Big_Pit\Tests\Feature;

use Alley\WP\Big_Pit\Big_Pit;
use Alley\WP\Big_Pit\Tests\TestCase;

/**
 * Test the default Big Pit client.
 */
final class BigPitTest extends TestCase {
	/**
	 * Test CRUD operations.
	 */
	public function test_crud() {
		$client = new Big_Pit();
		$client->boot();
		$this->crud_assertions( $client );
	}
}
