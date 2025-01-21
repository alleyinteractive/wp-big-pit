# Big Pit

The Big Pit is a key-value WordPress database table for storing things that don't make sense to store as a post, term, option, or other core data type.

## Installation

You can install the package via Composer:

```bash
composer require alleyinteractive/wp-big-pit
```

## API

The `Alley\WP\Big_Pit\Client` interface describes the create-read-update-delete operations that can be used with the Big Pit. You can type hint against this interface when using Big Pit as a dependency.

```php
namespace Alley\WP\Big_Pit;

use Alley\WP\Types\Feature;

interface Client extends Feature {
	public function value( string $key, string $group ): mixed;

	public function set( string $key, mixed $value, string $group ): void;

	public function delete( string $key, string $group ): void;

	public function group( string $key ): iterable;

	public function flush_group( string $group ): void;
}
```

Each item in the Big Pit has a key and a group, much like the WordPress object cache. Each key is unique within a group.

`Client` extends the `Alley\WP\Types\Feature` interface from the [Type Extensions](https://github.com/alleyinteractive/wp-type-extensions) library, which includes a `boot()` method for performing side effects.

You must call `boot()` before using the client. If you are compiling features using the `Features` instance from Type Extensions, you can include the Big Pit client, and it will be booted with the rest of your feature classes.

## Usage

```php
<?php

use Alley\WP\Big_Pit;

$external_id  = 'abcdef12345';
$api_response = '{"id":"abcdef12345","title":"The Best Movie Ever","rating":5}';

$big_pit = new Big_Pit\Big_Pit();
$big_pit->boot();

$big_pit->set( $external_id, $api_response, 'movie_reviews' );
$big_pit->value( $external_id, 'movie_reviews' ); // '{"id":"abcdef12345","title":"The Best Movie Ever","rating":5}'
$big_pit->delete( $external_id, 'movie_reviews' );
$big_pit->flush_group( 'movie_reviews' );
```

### Speculative Client

The `Big_Speculative_Pit` decorator class tracks the items that are fetched during a given request and preloads those items in a single query the next time the same page is requested.

```php
<?php

use Alley\WP\Big_Pit;

$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

$big_pit = new Big_Pit\Big_Speculative_Pit(
  request: $request,
  origin: new Big_Pit\Big_Pit(),
);
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
