<?php
declare(strict_types=1);

namespace GRI\Admin;

/**
 * Admin menu handler
 * 
 * Creates the plugin settings page under WooCommerce menu.
 *
 * @package    Google_Review_Incentive
 * @author     Ahmad Wael <https://www.bbioon.com>
 * @since      1.0.0
 */
class Admin_Menu {

    /**
     * Add plugin menu to WooCommerce
     *
     * @since 1.0.0
     */
    public function add_plugin_menu(): void {
        add_submenu_page(
            'woocommerce',
            __( 'Google Review Incentive', 'google-review-incentive' ),
            __( 'Review Incentive', 'google-review-incentive' ),
            'manage_woocommerce',
            'google-review-incentive',
            [ $this, 'render_settings_page' ]
        );
    }

    /**
     * Render settings page
     *
     * @since 1.0.0
     */
    public function render_settings_page(): void {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        // Handle settings saved message
        if ( isset( $_GET['settings-updated'] ) ) {
            add_settings_error(
                'gri_messages',
                'gri_message',
                __( 'Settings saved successfully.', 'google-review-incentive' ),
                'updated'
            );
        }

        settings_errors( 'gri_messages' );
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

            <form action="options.php" method="post">
                <?php
                settings_fields( 'gri_settings_group' );
                do_settings_sections( 'google-review-incentive' );
                submit_button( __( 'Save Settings', 'google-review-incentive' ) );
                ?>
            </form>
        </div>
        <?php
    }
}
