<?php
/**
 * Item class file
 *
 * @package wp-big-pit
 */

namespace Alley\WP\Big_Pit;

/**
 * A single item retrieved from the DB.
 */
final class Item {
	/**
	 * Constructor.
	 *
	 * @param string $key    Item key.
	 * @param mixed  $value  Item value.
	 * @param string $group  Item group.
	 * @param Client $client Big Pit client.
	 */
	public function __construct(
		private readonly string $key,
		public readonly mixed $value,
		private readonly string $group,
		private readonly Client $client,
	) {}

	/**
	 * Delete the item.
	 */
	public function delete(): void {
		$this->client->delete( $this->key, $this->group );
	}
}
