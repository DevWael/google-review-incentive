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


	public function generate_coupon( string $customer_email, WC_Order $order ) {
		if ( empty( $customer_email ) || ! is_email( $customer_email ) ) {
			return false;
		}

		$coupon_code = $this->generate_unique_coupon_code( $customer_email );

		if ( wc_get_coupon_id_by_code( $coupon_code ) ) {
			return $coupon_code;
		}

		try {
			$coupon = new WC_Coupon();
			$coupon->set_code( $coupon_code );

			$coupon_type   = get_option( 'gri_coupon_type', 'percent' );
			$coupon_amount = (float) get_option( 'gri_coupon_amount', 10 );

			$coupon->set_discount_type( $coupon_type );
			$coupon->set_amount( $coupon_amount );

			$validity_days = absint( get_option( 'gri_coupon_validity', 30 ) );
			$expiry_date   = date( 'Y-m-d', strtotime( "+{$validity_days} days" ) );
			$coupon->set_date_expires( $expiry_date );

			$coupon->set_email_restrictions( array( $customer_email ) );
			$coupon->set_usage_limit( 1 );
			$coupon->set_usage_limit_per_user( 1 );
			$coupon->set_individual_use( true );

			$coupon->set_description( sprintf( __( 'Thank you coupon for %1$s (Order #%2$d)', 'google-review-incentive' ), $customer_email, $order->get_id() ) );

			$coupon->save();

			// Store coupon code for registered users
			$user = get_user_by( 'email', $customer_email );
			if ( $user ) {
				update_user_meta( $user->ID, '_gri_coupon_code', $coupon_code );
			}

			if ( function_exists( 'wc_get_logger' ) ) {
				$logger = wc_get_logger();
				$logger->info( sprintf( 'Generated coupon %s for %s (Order #%d)', $coupon_code, $customer_email, $order->get_id() ), array( 'source' => 'google-review-incentive' ) );
			}

			return $coupon_code;
		} catch ( \Exception $e ) {
			if ( function_exists( 'wc_get_logger' ) ) {
				$logger = wc_get_logger();
				$logger->error( sprintf( 'Failed to generate coupon for %s: %s', $customer_email, $e->getMessage() ), array( 'source' => 'google-review-incentive' ) );
			}

			return false;
		}
	}

	private function generate_unique_coupon_code( string $customer_email ): string {
		$prefix = 'REVIEW';
		$suffix = strtoupper( substr( md5( $customer_email . time() ), 0, 8 ) );
		return $prefix . '-' . $suffix;
	}
}
