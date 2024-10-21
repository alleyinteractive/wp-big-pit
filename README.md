# Big Pit

The Big Pit is a WordPress database table for storing the things that don't make sense to store as a post, term, option, or other core data type.

## Installation

You can install the package via Composer:

```bash
composer require alleyinteractive/wp-big-pit
```

## Usage

Each item in the Big Pit has a key and a group, much like the WordPress object cache. Each key is unique within a group.

```php

use Alley\WP\Big_Pit;

$external_id  = 'abcdef12345';
$api_response = '{"id":"abcdef12345","title":"The Best Movie Ever","rating":5}';

$big_pit = Big_Pit::instance();

$big_pit->set( $external_id, $api_response, 'movie_reviews' );
$big_pit->get( $external_id, 'movie_reviews' ); // '{"id":"abcdef12345","title":"The Best Movie Ever","rating":5}'
$big_pit->delete( $external_id, 'movie_reviews' );
$big_pit->flush_group( 'movie_reviews' );
```

## About

### License

[GPL-2.0-or-later](https://github.com/alleyinteractive/wp-big-pit/blob/main/LICENSE)

### Maintainers

[Alley Interactive](https://github.com/alleyinteractive)
