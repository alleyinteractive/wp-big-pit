# Changelog

This library adheres to [Semantic Versioning](https://semver.org/) and [Keep a CHANGELOG](https://keepachangelog.com/en/1.0.0/).

## 0.5.0

### Added

- `Client` interface, extending `Alley\WP\Types\Feature`, for implementing a Big Pit client.
- `Items` class for clients that want to keep their own in-memory cache of fetched items.
- `Big_Speculative_Pit` client for preloading items used the last time a URL was requested.

### Changed

- `Big_Pit` is now in the `Alley\WP\Big_Pit` subnamespace, implements the `Client` interface, and must be instantiated with the `new` keyword.
- The `boot()` method on clients must be called manually.
- The `$wpdb->big_pit` property will be unset if the table is not available.

### Removed

- `Big_Pit::instance()` method.

## 0.4.0

### Added

- In-memory cache of items fetched during the request.

## 0.3.0

### Changed

- When storing items with the PSR-16 adapter, the group name is now automatically prefixed to avoid unintended data loss when flushing the cache.

### Fixed

- Incorrect database table prefix in multisite installations.

## 0.2.0

### Added

- PSR-16 cache adapter.

### Changed

- The minimum PHP version is now 8.1.

## 0.1.0

- Initial release
