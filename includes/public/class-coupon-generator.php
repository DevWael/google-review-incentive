<?php
declare(strict_types=1);

namespace GRI\Public_Module;

use WC_Coupon;
use WC_Order;

/**
 * Coupon generator
 * 
 * Handles programmatic generation of WooCommerce coupons.
 *
 * @package    Google_Review_Incentive
 * @author     Ahmad Wael
 * @since      1.0.0
 */
class Coupon_Generator {

    public function generate_coupon( int $customer_id, WC_Order $order ) {
        $customer = get_userdata( $customer_id );
        if ( ! $customer ) {
            return false;
        }

        $coupon_code = $this->generate_unique_coupon_code( $customer_id );

        if ( wc_get_coupon_id_by_code( $coupon_code ) ) {
            return $coupon_code;
        }

        try {
            $coupon = new WC_Coupon();
            $coupon->set_code( $coupon_code );

            $coupon_type = get_option( 'gri_coupon_type', 'percent' );
            $coupon_amount = (float) get_option( 'gri_coupon_amount', 10 );

            $coupon->set_discount_type( $coupon_type );
            $coupon->set_amount( $coupon_amount );

            $validity_days = absint( get_option( 'gri_coupon_validity', 30 ) );
            $expiry_date = date( 'Y-m-d', strtotime( "+{$validity_days} days" ) );
            $coupon->set_date_expires( $expiry_date );

            $coupon->set_email_restrictions( [ $customer->user_email ] );
            $coupon->set_usage_limit( 1 );
            $coupon->set_usage_limit_per_user( 1 );
            $coupon->set_individual_use( true );

            $coupon->set_description( sprintf( __( 'Thank you coupon for customer %s (Order #%d)', 'google-review-incentive' ), $customer->user_email, $order->get_id() ) );

            $coupon->save();

            update_user_meta( $customer_id, '_gri_coupon_code', $coupon_code );

            if ( function_exists( 'wc_get_logger' ) ) {
                $logger = wc_get_logger();
                $logger->info( sprintf( 'Generated coupon %s for customer #%d (Order #%d)', $coupon_code, $customer_id, $order->get_id() ), [ 'source' => 'google-review-incentive' ] );
            }

            return $coupon_code;

        } catch ( \Exception $e ) {
            if ( function_exists( 'wc_get_logger' ) ) {
                $logger = wc_get_logger();
                $logger->error( sprintf( 'Failed to generate coupon for customer #%d: %s', $customer_id, $e->getMessage() ), [ 'source' => 'google-review-incentive' ] );
            }

            return false;
        }
    }

    private function generate_unique_coupon_code( int $customer_id ): string {
        $prefix = 'REVIEW';
        $suffix = strtoupper( substr( md5( $customer_id . time() ), 0, 8 ) );
        return $prefix . '-' . $suffix;
    }
}
