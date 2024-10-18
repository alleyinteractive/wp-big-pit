<?php
/**
 * Big_Pit class file
 *
 * @package wp-big-pit
 */

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching

namespace Alley\WP;

use Alley\WP\Types\Feature;

/**
 * The Big Pit.
 */
final class Big_Pit implements Feature {
	/**
	 * Singleton.
	 *
	 * @var self
	 */
	private static ?self $instance = null;

	/**
	 * Whether the database is ready to accept queries.
	 *
	 * @var bool
	 */
	private bool $ready = false;

	/**
	 * Instance.
	 *
	 * @return self
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->boot();
		}

		return self::$instance;
	}

	/**
	 * Boot the feature.
	 */
	public function boot(): void {
		global $wpdb;

		$wpdb->big_pit = $wpdb->base_prefix . 'big_pit';

		if ( defined( 'WP_INSTALLING' ) ) {
			return;
		}

		try {
			$this->upsert();
			$this->ready = true;
		} catch ( \Exception $e ) {
			// Do nothing.
			unset( $e );
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

		if ( ! $this->ready ) {
			return null;
		}

		$value = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT item_value FROM {$wpdb->big_pit} WHERE item_group = %s AND item_key = %s LIMIT 1",
				$group,
				$key
			),
		);

		if ( is_string( $value ) ) {
			$value = maybe_unserialize( $value );
		}

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

		if ( ! $this->ready ) {
			return;
		}

		$value = maybe_serialize( $value ); // @phpstan-ignore argument.type

		$exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT item_id FROM {$wpdb->big_pit} WHERE item_group = %s AND item_key = %s LIMIT 1",
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
	}

	/**
	 * Delete a value.
	 *
	 * @param string $key   Item key.
	 * @param string $group Item group.
	 */
	public function delete( string $key, string $group ): void {
		global $wpdb;

		if ( ! $this->ready ) {
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
	}

	/**
	 * Delete all values in a group.
	 *
	 * @param string $group Item group.
	 */
	public function flush_group( string $group ): void {
		global $wpdb;

		if ( ! $this->ready ) {
			return;
		}

		$wpdb->delete(
			$wpdb->big_pit,
			[
				'item_group' => $group,
			],
			[ '%s' ],
		);
	}

	/**
	 * Create or update the database table.
	 *
	 * @throws \Exception If database operations fail.
	 */
	private function upsert(): void {
		global $wpdb;

		$available_version = '1';
		$installed_version = get_option( 'wp_big_pit_database_version', '0' );

		if ( $available_version === $installed_version ) {
			return;
		}

		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . '/wp-admin/includes/upgrade.php';
		}

		if ( ! $installed_version ) {
			$delta = \dbDelta(
				<<<SQL
CREATE TABLE {$wpdb->big_pit} (
	item_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	item_group varchar(255) NOT NULL,
	item_key varchar(255) NOT NULL,
	item_value longtext NOT NULL
) {$wpdb->get_charset_collate()};
SQL
			);

			if ( ! isset( $delta[ $wpdb->big_pit ] ) ) {
				throw new \Exception( 'Failed to create table.' );
			}

			$installed_version = '1';
		}

		update_option( 'wp_big_pit_database_version', $installed_version );
	}
}
