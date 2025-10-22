<?php
declare(strict_types=1);

/**
 * Uninstall script
 *
 * @package    Google_Review_Incentive
 * @author     Ahmad Wael
 * @since      1.0.0
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Delete plugin options
$options = [
    'gri_enable_coupon',
    'gri_coupon_type',
    'gri_coupon_amount',
    'gri_coupon_validity',
    'gri_email_delay',
    'gri_link_text',
    'gri_google_place_id',
    'gri_email_subject',
    'gri_email_content',
];

foreach ( $options as $option ) {
    delete_option( $option );
}

// Delete user meta
global $wpdb;

$wpdb->query(
    "DELETE FROM {$wpdb->usermeta} 
     WHERE meta_key LIKE '_gri_%'"
);

// Delete generated coupons
$coupon_codes = $wpdb->get_col(
    "SELECT meta_value FROM {$wpdb->usermeta} 
     WHERE meta_key = '_gri_coupon_code'"
);

foreach ( $coupon_codes as $coupon_code ) {
    $coupon_id = wc_get_coupon_id_by_code( $coupon_code );
    if ( $coupon_id ) {
        wp_delete_post( $coupon_id, true );
    }
}

// Clear scheduled actions
if ( function_exists( 'as_unschedule_all_actions' ) ) {
    as_unschedule_all_actions( 'gri_send_coupon_email', [], 'google-review-incentive' );
}
