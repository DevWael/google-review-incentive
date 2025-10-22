<?php
declare(strict_types=1);

namespace GRI;

/**
 * Plugin activator
 *
 * @package    Google_Review_Incentive
 * @author     Ahmad Wael
 * @since      1.0.0
 */
class Activator {

    public static function activate(): void {
        if ( ! class_exists( 'WooCommerce' ) ) {
            wp_die(
                esc_html__( 'Google Review Incentive requires WooCommerce to be installed and active.', 'google-review-incentive' ),
                esc_html__( 'Plugin Activation Error', 'google-review-incentive' ),
                [ 'back_link' => true ]
            );
        }

        if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
            wp_die(
                esc_html__( 'Google Review Incentive requires PHP 7.4 or higher.', 'google-review-incentive' ),
                esc_html__( 'Plugin Activation Error', 'google-review-incentive' ),
                [ 'back_link' => true ]
            );
        }

        self::set_default_options();
        flush_rewrite_rules();
    }

    private static function set_default_options(): void {
        $defaults = [
            'gri_enable_coupon' => true,
            'gri_coupon_type' => 'percent',
            'gri_coupon_amount' => 15,
            'gri_coupon_validity' => 30,
            'gri_email_delay' => 60,
            'gri_link_text' => __( 'Share your experience on Google', 'google-review-incentive' ),
            'gri_google_place_id' => '',
            'gri_email_subject' => __( 'Thank you for your review! Here\'s your reward', 'google-review-incentive' ),
            'gri_email_content' => self::get_default_email_content(),
        ];

        foreach ( $defaults as $key => $value ) {
            if ( false === get_option( $key ) ) {
                add_option( $key, $value );
            }
        }
    }

    private static function get_default_email_content(): string {
        return sprintf( __( 'Thank you for taking the time to share your experience!%sAs a token of our appreciation, here is your exclusive coupon code: <strong>{coupon_code}</strong>%sUse this code on your next purchase to receive your discount.%sBest regards,', 'google-review-incentive' ), "\n\n", "\n\n", "\n\n" );
    }
}
