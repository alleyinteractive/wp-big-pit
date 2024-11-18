<?php
/**
 * Client interface file
 *
 * @package wp-big-pit
 */

namespace Alley\WP\Big_Pit;

use Alley\WP\Types\Feature;

/**
 * Describes a Big Pit client.
 */
interface Client extends Feature {
	/**
	 * Get a value.
	 *
	 * @param string $key   Item key.
	 * @param string $group Item group.
	 * @return mixed|null
	 */
	public function get( string $key, string $group ): mixed;

	/**
	 * Set a value.
	 *
	 * @param string $key   Item key.
	 * @param mixed  $value Item value.
	 * @param string $group Item group.
	 */
	public function set( string $key, mixed $value, string $group ): void;

	/**
	 * Delete a value.
	 *
	 * @param string $key   Item key.
	 * @param string $group Item group.
	 */
	public function delete( string $key, string $group ): void;

	/**
	 * Delete all values in a group.
	 *
	 * @param string $group Item group.
	 */
	public function flush_group( string $group ): void;
}
