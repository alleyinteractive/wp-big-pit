<?php
/**
 * Preloaded_Client class file
 *
 * @package wp-big-pit
 */

namespace Alley\WP\Big_Pit;

use Symfony\Component\HttpFoundation\Request;

/**
 * Track the keys fetched during a given request and preload those values the next time the same request occurs.
 */
final class Big_Speculative_Pit implements Client {
	/**
	 * Previously saved keys by group.
	 *
	 * @phpstan-var array<string, array<string>>
	 *
	 * @var array[]
	 */
	private array $saved_keys = [];

	/**
	 * Fetched keys by group.
	 *
	 * @phpstan-var array<string, array<string>>
	 *
	 * @var array[]
	 */
	private array $fetched_keys = [];

	/**
	 * Preloaded items.
	 *
	 * @var Items
	 */
	private readonly Items $items;

	/**
	 * Constructor.
	 *
	 * @param Request $request Current request.
	 * @param Client  $origin  Client instance.
	 */
	public function __construct(
		private readonly Request $request,
		private readonly Client $origin,
	) {
		$this->items = new Items();
	}

	/**
	 * Boot the feature.
	 */
	public function boot(): void {
		// Since this class isn't responsible for creating the table, make sure upstream classes can do so.
		$this->origin->boot();

		/*
		 * Preload once here and be done with it, rather than having to make sure in every method that values were
		 * preloaded and that preloading happens only once. Trades a DB query for a simpler implementation.
		 */
		$this->preload();

		// Priority 0 so that it's visible in Query Monitor.
		add_action( 'shutdown', [ $this, 'on_shutdown' ], 0 );
	}

	/**
	 * Get a value.
	 *
	 * @param string $key   Item key.
	 * @param string $group Item group.
	 * @return mixed|null
	 */
	public function get( string $key, string $group ): mixed {
		$this->fetched_keys[ $group ][] = $key;

		if ( $this->items->has( $key, $group ) ) {
			return $this->items->get( $key, $group );
		}

		return $this->origin->get( $key, $group );
	}

	/**
	 * Set a value.
	 *
	 * @param string $key   Item key.
	 * @param mixed  $value Item value.
	 * @param string $group Item group.
	 */
	public function set( string $key, mixed $value, string $group ): void {
		$this->items->remove( $key, $group );
		$this->origin->set( $key, $value, $group );
	}

	/**
	 * Delete a value.
	 *
	 * @param string $key   Item key.
	 * @param string $group Item group.
	 */
	public function delete( string $key, string $group ): void {
		$this->items->remove( $key, $group );
		$this->origin->delete( $key, $group );
	}

	/**
	 * Delete all values in a group.
	 *
	 * @param string $group Item group.
	 */
	public function flush_group( string $group ): void {
		$this->items->remove_group( $group );
		$this->origin->flush_group( $group );
	}

	/**
	 * Save the keys that were fetched if they changed.
	 */
	public function on_shutdown(): void {
		// Sort the fetched keys to ensure consistent ordering.
		ksort( $this->fetched_keys );
		foreach ( array_keys( $this->fetched_keys ) as $group ) {
			$this->fetched_keys[ $group ] = array_unique( $this->fetched_keys[ $group ] );
			sort( $this->fetched_keys[ $group ] );
		}

		if (
			$this->fetched_keys !== $this->saved_keys
			&& ( count( $this->fetched_keys ) > 0 || count( $this->saved_keys ) > 0 )
		) {
			$this->origin->set( $this->key(), $this->fetched_keys, 'big_speculative_pit' );
		}
	}

	/**
	 * Preload the values.
	 */
	private function preload(): void {
		global $wpdb;

		assert( $wpdb instanceof \wpdb );

		if ( ! isset( $wpdb->big_pit ) ) {
			return;
		}

		$saved = $this->origin->get( $this->key(), 'big_speculative_pit' );

		$this->saved_keys = is_array( $saved ) ? $saved : [];

		foreach ( $this->saved_keys as $group => $keys ) {
			$this->items->remove_group( $group );

			$items = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
					'SELECT item_key, item_value'
					. ' FROM %i'
					. ' WHERE item_group = %s'
					. ' AND item_key IN (' . implode( ',', array_fill( 0, count( $keys ), '%s' ) ) . ')',
					$wpdb->big_pit,
					$group,
					...$keys,
				),
			);

			if ( is_array( $items ) ) {
				$items = array_column( $items, 'item_value', 'item_key' );

				foreach ( $keys as $key ) {
					$value = $items[ $key ] ?? null;

					if ( is_string( $value ) ) {
						$value = maybe_unserialize( $value );
					}

					$this->items->add( $key, $value, $group );
				}
			}
		}
	}

	/**
	 * Key for storing keys for this request.
	 *
	 * @return string
	 */
	private function key(): string {
		return $this->request->getUri();
	}
}
