<?php
declare(strict_types=1);

namespace GRI;

/**
 * Main plugin class
 *
 * Coordinates all plugin components and initializes hooks.
 * Implements dependency injection for better testability.
 *
 * @package    Google_Review_Incentive
 * @author     Ahmad Wael <https://www.bbioon.com>
 * @since      1.0.0
 */
class Google_Review_Incentive {

	/**
	 * Admin menu handler
	 *
	 * @var Admin\Admin_Menu
	 */
	protected $admin_menu;

	/**
	 * Admin settings handler
	 *
	 * @var Admin\Admin_Settings
	 */
	protected $admin_settings;

	/**
	 * Email handler
	 *
	 * @var Public_Module\Email_Handler
	 */
	protected $email_handler;

	/**
	 * Review link handler
	 *
	 * @var Public_Module\Review_Link_Handler
	 */
	protected $review_link_handler;

	/**
	 * Initialize the plugin
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load required dependencies
	 *
	 * @since 1.0.0
	 */
	private function load_dependencies(): void {
		// Admin dependencies
		require_once GRI_PLUGIN_DIR . 'includes/admin/class-admin-menu.php';
		require_once GRI_PLUGIN_DIR . 'includes/admin/class-admin-settings.php';

		// Public dependencies
		require_once GRI_PLUGIN_DIR . 'includes/public/class-email-handler.php';
		require_once GRI_PLUGIN_DIR . 'includes/public/class-review-link-handler.php';
		require_once GRI_PLUGIN_DIR . 'includes/public/class-coupon-generator.php';
		require_once GRI_PLUGIN_DIR . 'includes/public/class-customer-tracker.php';

		// Initialize components
		$this->admin_menu          = new Admin\Admin_Menu();
		$this->admin_settings      = new Admin\Admin_Settings();
		$this->email_handler       = new Public_Module\Email_Handler();
		$this->review_link_handler = new Public_Module\Review_Link_Handler();
	}

	/**
	 * Define admin-specific hooks
	 *
	 * @since 1.0.0
	 */
	private function define_admin_hooks(): void {
		add_action( 'admin_menu', array( $this->admin_menu, 'add_plugin_menu' ) );
		add_action( 'admin_init', array( $this->admin_settings, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Define public-facing hooks
	 *
	 * @since 1.0.0
	 */
	private function define_public_hooks(): void {
		add_action( 'woocommerce_email_after_order_table', array( $this->email_handler, 'add_review_link' ), 10, 4 );
		add_action( 'template_redirect', array( $this->review_link_handler, 'handle_review_link_click' ) );
		add_action( 'gri_send_coupon_email', array( $this->email_handler, 'send_coupon_email' ), 10, 2 );
	}

	/**
	 * Enqueue admin assets
	 *
	 * @since 1.0.0
	 * @param string $hook Current admin page hook
	 */
	public function enqueue_admin_assets( string $hook ): void {
		if ( 'woocommerce_page_google-review-incentive' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'gri-admin-styles',
			GRI_PLUGIN_URL . 'assets/css/admin-styles.css',
			array(),
			GRI_VERSION
		);

		wp_enqueue_script(
			'gri-admin-scripts',
			GRI_PLUGIN_URL . 'assets/js/admin-scripts.js',
			array( 'jquery' ),
			GRI_VERSION,
			true
		);
	}

	/**
	 * Run the plugin
	 *
	 * @since 1.0.0
	 */
	public function run(): void {
		// Plugin is initialized via hooks
	}
}
