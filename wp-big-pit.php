<?php
/**
 * Plugin Name: Big Pit
 * Plugin URI: https://github.com/alleyinteractive/wp-big-pit
 * Description: The WordPress database table for everything else.
 * Version: 0.1.0
 * Author: Alley
 * Author URI: https://github.com/alleyinteractive/wp-big-pit
 * Requires at least: 6.6
 * Tested up to: 6.6
 *
 * Text Domain: wp-big-pit
 * Domain Path: /languages/
 *
 * @package wp-big-pit
 */

namespace Alley\WP\Big_Pit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Root directory to this plugin.
 */
define( 'WP_BIG_PIT_DIR', __DIR__ );

// Check if Composer is installed (remove if Composer is not required for your plugin).
if ( ! file_exists( __DIR__ . '/vendor/wordpress-autoload.php' ) ) {
	// Will also check for the presence of an already loaded Composer autoloader
	// to see if the Composer dependencies have been installed in a parent
	// folder. This is useful for when the plugin is loaded as a Composer
	// dependency in a larger project.
	if ( ! class_exists( \Composer\InstalledVersions::class ) ) {
		\add_action(
			'admin_notices',
			function () {
				?>
				<div class="notice notice-error">
					<p><?php esc_html_e( 'Composer is not installed and wp-big-pit cannot load. Try using a `*-built` branch if the plugin is being loaded as a submodule.', 'wp-big-pit' ); ?></p>
				</div>
				<?php
			}
		);

		return;
	}
} else {
	// Load Composer dependencies.
	require_once __DIR__ . '/vendor/wordpress-autoload.php';
}
