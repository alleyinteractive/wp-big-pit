<?php
/**
 * Big_Pit class file
 *
 * @package wp-big-pit
 */

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching

namespace Alley\WP\Big_Pit;

/**
 * The Big Pit.
 */
final class Big_Pit implements Client {
	/**
	 * Fetched items.
	 *
	 * @var Items
	 */
	private readonly Items $items;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->items = new Items();
	}

	/**
	 * Boot the feature.
	 */
	public function boot(): void {
		global $wpdb;

		$wpdb->big_pit = $wpdb->get_blog_prefix() . 'big_pit';

		if ( defined( 'WP_INSTALLING' ) ) {
			return;
		}

		try {
			$this->upsert();
		} catch ( \Exception $e ) {
			unset( $wpdb->big_pit );
		}
	}

	/**
	 * Get a value.
	 *
	 * @param string $key   Item key.
	 * @param string $group Item group.
	 * @return mixed|null
	 */
	public function get( string $key, string $group ): mixed {
		global $wpdb;

		assert( $wpdb instanceof \wpdb );

		if ( ! isset( $wpdb->big_pit ) ) {
			return null;
		}

		if ( $this->items->has( $key, $group ) ) {
			return $this->items->get( $key, $group );
		}

		$value = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT item_value FROM %i WHERE item_group = %s AND item_key = %s LIMIT 1',
				$wpdb->big_pit,
				$group,
				$key
			),
		);

		if ( is_string( $value ) ) {
			$value = maybe_unserialize( $value );
		}

		$this->items->add( $key, $value, $group );

		return $value;
	}

	/**
	 * Set a value.
	 *
	 * @param string $key   Item key.
	 * @param mixed  $value Item value.
	 * @param string $group Item group.
	 */
	public function set( string $key, mixed $value, string $group ): void {
		global $wpdb;

		assert( $wpdb instanceof \wpdb );

		if ( ! isset( $wpdb->big_pit ) ) {
			return;
		}

		$value = maybe_serialize( $value ); // @phpstan-ignore argument.type

		$exists = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT item_id FROM %i WHERE item_group = %s AND item_key = %s LIMIT 1',
				$wpdb->big_pit,
				$group,
				$key
			),
		);

		if ( $exists ) {
			$wpdb->update(
				$wpdb->big_pit,
				[
					'item_value' => $value,
				],
				[
					'item_id' => $exists,
				],
				[ '%s' ],
				[ '%s' ],
			);
		} else {
			$wpdb->insert(
				$wpdb->big_pit,
				[
					'item_group' => $group,
					'item_key'   => $key,
					'item_value' => $value,
				],
				[ '%s', '%s', '%s' ],
			);
		}

		$this->items->remove( $key, $group );
	}

	/**
	 * Delete a value.
	 *
	 * @param string $key   Item key.
	 * @param string $group Item group.
	 */
	public function delete( string $key, string $group ): void {
		global $wpdb;

		assert( $wpdb instanceof \wpdb );

		if ( ! isset( $wpdb->big_pit ) ) {
			return;
		}

		$wpdb->delete(
			$wpdb->big_pit,
			[
				'item_group' => $group,
				'item_key'   => $key,
			],
			[ '%s', '%s' ],
		);

		$this->items->remove( $key, $group );
	}

	/**
	 * Get all items in a group.
	 *
	 * @param string $group Item group.
	 * @return Item[]
	 */
	public function group( string $group ): iterable {
		global $wpdb;

		assert( $wpdb instanceof \wpdb );

		if ( ! isset( $wpdb->big_pit ) ) {
			return [];
		}

		$min_id = 0;

		do {
			$results = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT * FROM %i WHERE item_group = %s AND item_id > %d ORDER BY item_id LIMIT 100',
					$wpdb->big_pit,
					$group,
					$min_id
				),
				ARRAY_A,
			);

			$count   = is_countable( $results ) ? count( $results ) : 0;
			$min_id += $count;

			if ( is_array( $results ) ) {
				foreach ( $results as $result ) {
					if (
						! isset( $result['item_id'], $result['item_key'], $result['item_value'] )
						|| ! is_numeric( $result['item_id'] )
						|| ! is_string( $result['item_key'] )
						|| ! is_string( $result['item_value'] )
					) {
						continue;
					}

					$min_id = max( $min_id, (int) $result['item_id'] );

					yield new Item( $result['item_key'], maybe_unserialize( $result['item_value'] ), $group, $this );
				}
			}
		} while ( $count > 0 );
	}

	/**
	 * Delete all values in a group.
	 *
	 * @param string $group Item group.
	 */
	public function flush_group( string $group ): void {
		global $wpdb;

		assert( $wpdb instanceof \wpdb );

		if ( ! isset( $wpdb->big_pit ) ) {
			return;
		}

		$wpdb->delete(
			$wpdb->big_pit,
			[
				'item_group' => $group,
			],
			[ '%s' ],
		);

		$this->items->remove_group( $group );
	}

	/**
	 * Create or update the database table.
	 *
	 * @throws \Exception If database operations fail.
	 */
	private function upsert(): void {
		global $wpdb;

		assert( $wpdb instanceof \wpdb );

		$available_version = '2';
		$installed_version = get_option( 'wp_big_pit_database_version', '0' );

		if ( $available_version === $installed_version ) {
			return;
		}

		if ( ! isset( $wpdb->big_pit ) ) {
			return;
		}

		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . '/wp-admin/includes/upgrade.php';
		}

		// Ensure the database version is at least version 2.
		if ( ! $installed_version || '1' === $installed_version ) {
			/*
			 * Create the table if this is a new installation or if this is a site in
			 * a multisite that didn't get a site-specific table at version 1.
			 */
			if ( ! $installed_version || $wpdb->blogid > 1 ) {
				$delta = \dbDelta(
					<<<SQL
CREATE TABLE {$wpdb->big_pit} (
	item_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	item_group varchar(255) NOT NULL,
	item_key varchar(255) NOT NULL,
	item_value longtext NOT NULL,
	KEY group_key (item_group(191),item_key(191)),
	KEY key_value (item_key(191), item_value(100))
) {$wpdb->get_charset_collate()};
SQL
				);

				if ( ! isset( $delta[ $wpdb->big_pit ] ) ) {
					throw new \Exception( 'Failed to create table.' );
				}
			}

			$installed_version = '2';
		}

		update_option( 'wp_big_pit_database_version', $installed_version );
	}
}
