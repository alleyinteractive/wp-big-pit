# Changelog

This library adheres to [Semantic Versioning](https://semver.org/) and [Keep a CHANGELOG](https://keepachangelog.com/en/1.0.0/).

## 0.3.0

## Changed

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
