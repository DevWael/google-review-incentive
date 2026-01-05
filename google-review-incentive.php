<?php
/**
 * Plugin Name: Google Review Incentive for WooCommerce
 * Plugin URI: https://www.bbioon.com
 * Description: Encourages customers to leave Google reviews by offering one-time coupon codes after order completion.
 * Version: 1.0.0
 * Author: Ahmad Wael
 * Author URI: https://www.bbioon.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: google-review-incentive
 * Domain Path: /languages
 * Requires PHP: 7.4
 * Requires at least: 5.8
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 *
 * @package Google_Review_Incentive
 */

declare(strict_types=1);

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define plugin constants.
define( 'GRI_VERSION', '1.0.0' );
define( 'GRI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GRI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'GRI_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

/**
 * Check if WooCommerce is active.
 */
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
	add_action( 'admin_notices', 'gri_woocommerce_missing_notice' );
	return;
}

/**
 * Display admin notice if WooCommerce is not active.
 */
function gri_woocommerce_missing_notice(): void {
	?>
	<div class="error">
		<p><?php esc_html_e( 'Google Review Incentive requires WooCommerce to be installed and active.', 'google-review-incentive' ); ?></p>
	</div>
	<?php
}

// Autoloader.
spl_autoload_register(
	function ( $autoload_class ) {
		$prefix   = 'GRI\\';
		$base_dir = GRI_PLUGIN_DIR . 'includes/';

		$len = strlen( $prefix );
		if ( strncmp( $prefix, $autoload_class, $len ) !== 0 ) {
			return;
		}

		$relative_class = substr( $autoload_class, $len );
		$file           = $base_dir . 'class-' . strtolower( str_replace( '\\', '-', $relative_class ) ) . '.php';

		if ( file_exists( $file ) ) {
			require $file;
		}
	}
);

// Include core files.
require_once GRI_PLUGIN_DIR . 'includes/class-google-review-incentive.php';
require_once GRI_PLUGIN_DIR . 'includes/class-activator.php';
require_once GRI_PLUGIN_DIR . 'includes/class-deactivator.php';

// Activation and deactivation hooks.
register_activation_hook( __FILE__, array( 'GRI\\Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'GRI\\Deactivator', 'deactivate' ) );

/**
 * Begin plugin execution.
 */
function run_google_review_incentive(): void {
	$plugin = new GRI\Google_Review_Incentive();
	$plugin->run();
}

run_google_review_incentive();
