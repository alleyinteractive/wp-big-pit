<?php
/**
 * Items class file
 *
 * @package wp-big-pit
 */

namespace Alley\WP\Big_Pit;

/**
 * Items retrieved from the DB.
 */
final class Items {
	/**
	 * Cached values.
	 *
	 * @phpstan-var array<string, array<string, mixed>>
	 *
	 * @var array[]
	 */
	private array $cache = [];

	/**
	 * Check if a value exists.
	 *
	 * @param string $key   Item key.
	 * @param string $group Item group.
	 * @return bool
	 */
	public function has( string $key, string $group ): bool {
		return isset( $this->cache[ $group ] ) && array_key_exists( $key, $this->cache[ $group ] );
	}

	/**
	 * Get a value.
	 *
	 * @param string $key   Item key.
	 * @param string $group Item group.
	 * @return mixed
	 */
	public function get( string $key, string $group ): mixed {
		$value = $this->cache[ $group ][ $key ];

		if ( is_object( $value ) ) {
			// Don't reuse the same instance across multiple calls.
			$value = clone $value;
		}

		return $value;
	}

	/**
	 * Add a value.
	 *
	 * @param string $key   Item key.
	 * @param mixed  $value Item value.
	 * @param string $group Item group.
	 */
	public function add( string $key, mixed $value, string $group ): void {
		$this->cache[ $group ][ $key ] = $value;
	}

	/**
	 * Remove a value.
	 *
	 * @param string $key   Item key.
	 * @param string $group Item group.
	 */
	public function remove( string $key, string $group ): void {
		unset( $this->cache[ $group ][ $key ] );
	}

	/**
	 * Remove a group.
	 *
	 * @param string $group Item group.
	 */
	public function remove_group( string $group ): void {
		unset( $this->cache[ $group ] );
	}
}
