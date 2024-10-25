# Big Pit

The Big Pit is a key-value WordPress database table for storing things that don't make sense to store as a post, term, option, or other core data type.

## Installation

You can install the package via Composer:

```bash
composer require alleyinteractive/wp-big-pit
```

## Usage

Each item in the Big Pit has a key and a group, much like the WordPress object cache. Each key is unique within a group.

### Direct Access

You can perform CRUD operations directly on The Pit:

```php
<?php

use Alley\WP\Big_Pit;

$external_id  = 'abcdef12345';
$api_response = '{"id":"abcdef12345","title":"The Best Movie Ever","rating":5}';

$big_pit = Big_Pit::instance();

$big_pit->set( $external_id, $api_response, 'movie_reviews' );
$big_pit->get( $external_id, 'movie_reviews' ); // '{"id":"abcdef12345","title":"The Best Movie Ever","rating":5}'
$big_pit->delete( $external_id, 'movie_reviews' );
$big_pit->flush_group( 'movie_reviews' );
```

### PSR-16 Cache Adapter

A PSR-16 adapter is available for caching data in The Pit:

```php
<?php

// Each instance of the cache adapter is bound to a group. Create different instances to save to different groups.
$cache = \Alley\WP\SimpleCache\Big_Pit_Adapter::create( group: 'movie_reviews' );

$cache->get( /* ... */ );
$cache->set( /* ... */ );
$cache->delete( /* ... */ );
$cache->clear();
// etc.
```

Note that the cache adapter will store data in a custom array structure, as described [in the wp-psr16 README](https://github.com/alleyinteractive/wp-psr16/blob/5ff411661f9682b3184dab596180a7a3edcaf446/README.md#implementation-details).

## Why Not Use Options?

There's nothing wrong with using options for storing key-value data, but it comes with overhead, including:

- Managing autoloading and the `alloptions` cache.
- `pre_option_` and `option_` filters on the values.
- Settings registration and default values.

Big Pit doesn't have autoloading, filters, or registered keys, so it might work for you if you don't need these features.

Or, you might plan to store thousands of rows, and you don't want to dilute the options table with that amount of data.

## About

### License

[GPL-2.0-or-later](https://github.com/alleyinteractive/wp-big-pit/blob/main/LICENSE)

### Maintainers

[Alley Interactive](https://github.com/alleyinteractive)
