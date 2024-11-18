<?php
/**
 * Big Pit Tests: Speculative Pit Feature Test
 *
 * @package wp-big-pit
 */

namespace Alley\WP\Big_Pit\Tests\Feature;

use Alley\WP\Big_Pit\Big_Pit;
use Alley\WP\Big_Pit\Big_Speculative_Pit;
use Alley\WP\Big_Pit\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Test the Big Speculative Pit client.
 */
final class BigSpeculativePitTest extends TestCase {
	/**
	 * Test CRUD operations.
	 */
	public function test_crud() {
		$request = Request::createFromGlobals();

		$client = new Big_Speculative_Pit( $request, new Big_Pit() );
		$client->boot();
		$this->crud_assertions( $client );

		// Save keys to the database to test that CRUD operations also complete successfully after values are preloaded.
		$client->on_shutdown();

		$client = new Big_Speculative_Pit( $request, new Big_Pit() );
		$client->boot();
		$this->crud_assertions( $client );
	}
}
