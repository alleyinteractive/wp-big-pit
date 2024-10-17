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

Big_Pit::instance()->set( $external_id, $api_response, 'movie_reviews' );

Big_Pit::instance()->get( $external_id, 'movie_reviews' ); // '{"id":"abcdef12345","title":"The Best Movie Ever","rating":5}'

Big_Pit::instance()->delete( $external_id, 'movie_reviews' );

Big_Pit::instance()->flush_group( 'movie_reviews' );
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

## About

### License

[GPL-2.0-or-later](https://github.com/alleyinteractive/wp-big-pit/blob/main/LICENSE)

### Maintainers

[Alley Interactive](https://github.com/alleyinteractive)
