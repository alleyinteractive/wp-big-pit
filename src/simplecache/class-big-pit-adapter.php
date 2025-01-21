<?php
/**
 * Big_Pit_Adapter class file
 *
 * @package wp-psr16
 */

namespace Alley\WP\SimpleCache;

use Alley\WP\Big_Pit\Client;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Clock\NativeClock;

/**
 * PSR-16 implementation that caches data in the Big Pit.
 */
final class Big_Pit_Adapter implements CacheInterface {
	/**
	 * Constructor.
	 *
	 * @param Client $pit   Big Pit client.
	 * @param string $group Cache group.
	 */
	private function __construct(
		private readonly Client $pit,
		private readonly string $group,
	) {}

	/**
	 * Create an instance using the default composition.
	 *
	 * @throws Invalid_Argument_Exception For invalid arguments.
	 *
	 * @param string $group  Cache group.
	 * @param Client $client Big Pit client.
	 * @return CacheInterface
	 */
	public static function create( string $group, Client $client ): CacheInterface {
		return new PSR16_Compliant(
			clock: new NativeClock(),
			origin: new Prefixed_Keys(
				prefix: '_psr16_',
				origin: new Maximum_Key_Length(
					limit: 172,
					origin: new self(
						pit: $client,
						// Prefix the group so that `flush()` doesn't wipe out other data.
						group: "_psr16_{$group}",
					),
				),
			),
		);
	}

	/**
	 * Fetches a value from the cache.
	 *
	 * @throws \Psr\SimpleCache\InvalidArgumentException If the $key string is not a legal value.
	 *
	 * @param string $key     The unique key of this item in the cache.
	 * @param mixed  $default Default value to return if the key does not exist.
	 * @return mixed The value of the item from the cache, or $default in case of cache miss.
	 */
	public function get( string $key, mixed $default = null ): mixed {
		$value = $this->pit->value( $key, $this->group );

		return $value ?? $default;
	}

	/**
	 * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
	 *
	 * @throws \Psr\SimpleCache\InvalidArgumentException If the $key string is not a legal value.
	 *
	 * @param string                 $key   The key of the item to store.
	 * @param mixed                  $value The value of the item to store, must be serializable.
	 * @param null|int|\DateInterval $ttl   Optional. The TTL value of this item.
	 * @return bool True on success and false on failure.
	 */
	public function set( string $key, mixed $value, \DateInterval|int|null $ttl = null ): bool {
		$this->pit->set( $key, $value, $this->group );

		return true;
	}

	/**
	 * Delete an item from the cache by its unique key.
	 *
	 * @throws \Psr\SimpleCache\InvalidArgumentException If the $key string is not a legal value.
	 *
	 * @param string $key The unique cache key of the item to delete.
	 * @return bool True if the item was successfully removed. False if there was an error.
	 */
	public function delete( string $key ): bool {
		$this->pit->delete( $key, $this->group );

		return true;
	}

	/**
	 * Wipes clean the entire cache's keys.
	 *
	 * @return bool True on success and false on failure.
	 */
	public function clear(): bool {
		$this->pit->flush_group( $this->group );

		return true;
	}

	/**
	 * Obtains multiple cache items by their unique keys.
	 *
	 * @throws \Psr\SimpleCache\InvalidArgumentException If $keys is neither an array nor a Traversable, or if any of
	 *                                                   the $keys are not a legal value.
	 *
	 * @phpstan-param iterable<string> $keys
	 * @phpstan-return iterable<string, mixed>
	 *
	 * @param iterable $keys    A list of keys that can be obtained in a single operation.
	 * @param mixed    $default Default value to return for keys that do not exist.
	 * @return iterable A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
	 */
	public function getMultiple( iterable $keys, mixed $default = null ): iterable {
		$out = [];

		foreach ( $keys as $key ) {
			$out[ $key ] = $this->get( $key, $default );
		}

		return $out;
	}

	/**
	 * Persists a set of key => value pairs in the cache, with an optional TTL.
	 *
	 * @throws \Psr\SimpleCache\InvalidArgumentException If $keys is neither an array nor a Traversable, or if any of
	 *                                                    the $keys are not a legal value.
	 *
	 * @phpstan-param iterable<string, mixed> $values
	 *
	 * @param iterable               $values A list of key => value pairs for a multiple-set operation.
	 * @param null|int|\DateInterval $ttl    Optional. The TTL value of this item. If no value is sent and
	 *                                       the driver supports TTL then the library may set a default value
	 *                                       for it or let the driver take care of that.
	 * @return bool True on success and false on failure.
	 */
	public function setMultiple( iterable $values, \DateInterval|int|null $ttl = null ): bool {
		foreach ( $values as $key => $value ) {
			$this->set( $key, $value, $ttl );
		}

		return true;
	}

	/**
	 * Deletes multiple cache items in a single operation.
	 *
	 * @throws \Psr\SimpleCache\InvalidArgumentException If $keys is neither an array nor a Traversable, or if any of
	 *                                                     the $keys are not a legal value.
	 *
	 * @phpstan-param iterable<string> $keys
	 *
	 * @param iterable $keys A list of string-based keys to be deleted.
	 * @return bool True if the items were successfully removed. False if there was an error.
	 */
	public function deleteMultiple( iterable $keys ): bool {
		foreach ( $keys as $key ) {
			$this->delete( $key );
		}

		return true;
	}

	/**
	 * Determines whether an item is present in the cache.
	 *
	 * @throws \Psr\SimpleCache\InvalidArgumentException If the $key string is not a legal value.
	 *
	 * @param string $key The cache item key.
	 * @return bool
	 */
	public function has( string $key ): bool {
		$ref = new \stdClass();
		return $this->get( $key, $ref ) !== $ref;
	}
}
