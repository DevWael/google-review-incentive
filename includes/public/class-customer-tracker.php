<?php

/**
 * Customer Tracker Class
 *
 * @package    Google_Review_Incentive
 * @subpackage Public_Module
 * @author     Ahmad Wael
 * @since      1.0.0
 */

declare(strict_types=1);

namespace GRI\Public_Module;

/**
 * Customer tracker
 *
 * Tracks customer actions related to review links and coupons.
 *
 * @package    Google_Review_Incentive
 * @author     Ahmad Wael
 * @since      1.0.0
 */
class Customer_Tracker {


	private const META_KEY_CLICKED    = '_gri_review_link_clicked';
	private const META_KEY_COUPON     = '_gri_coupon_code';
	private const META_KEY_EMAIL_SENT = '_gri_coupon_sent_date';
	private const OPTION_GUEST_CLICKS = 'gri_guest_review_clicks';

	/**
	 * Check if a customer has clicked the review link (registered users).
	 *
	 * @param int $customer_id User ID.
	 * @return bool
	 */
	public function has_clicked_review_link( int $customer_id ): bool {
		return (bool) get_user_meta( $customer_id, self::META_KEY_CLICKED, true );
	}

	/**
	 * Check if an email has already claimed a coupon (for guests and registered users).
	 *
	 * @param string $email Customer email.
	 * @return bool
	 */
	public function has_email_claimed_coupon( string $email ): bool {
		if ( empty( $email ) ) {
			return false;
		}

		// Normalize email.
		$email = strtolower( trim( $email ) );

		// Check if registered user.
		$user = get_user_by( 'email', $email );
		if ( $user ) {
			return $this->has_clicked_review_link( $user->ID );
		}

		// Check guest tracking.
		$guest_clicks = get_option( self::OPTION_GUEST_CLICKS, array() );
		return isset( $guest_clicks[ $email ] );
	}

	/**
	 * Track email as having claimed a coupon (for guests).
	 *
	 * @param string $email Customer email.
	 * @param string $coupon_code Generated coupon code.
	 * @return bool
	 */
	public function track_email_claim( string $email, string $coupon_code = '' ): bool {
		if ( empty( $email ) ) {
			return false;
		}

		// Normalize email.
		$email = strtolower( trim( $email ) );

		// Don't track if already claimed.
		if ( $this->has_email_claimed_coupon( $email ) ) {
			return false;
		}

		// Get existing data.
		$guest_clicks = get_option( self::OPTION_GUEST_CLICKS, array() );

		// Add new entry.
		$guest_clicks[ $email ] = array(
			'timestamp'   => time(),
			'coupon_code' => $coupon_code,
		);

		// Update option with autoload = false.
		return update_option( self::OPTION_GUEST_CLICKS, $guest_clicks, false );
	}

	/**
	 * Track review link click for a customer.
	 *
	 * @param int $customer_id User ID.
	 * @return bool True if tracked successfully, false if already tracked.
	 */
	public function track_review_link_click( int $customer_id ): bool {
		if ( $this->has_clicked_review_link( $customer_id ) ) {
			return false;
		}

		$result = update_user_meta( $customer_id, self::META_KEY_CLICKED, true );
		update_user_meta( $customer_id, self::META_KEY_CLICKED . '_timestamp', time() );

		return (bool) $result;
	}

	/**
	 * Get customer's coupon code.
	 *
	 * @param int $customer_id User ID.
	 * @return string|false Coupon code or false if not found.
	 */
	public function get_customer_coupon_code( int $customer_id ) {
		return get_user_meta( $customer_id, self::META_KEY_COUPON, true );
	}

	/**
	 * Check if coupon email has been sent to customer.
	 *
	 * @param int $customer_id User ID.
	 * @return bool True if email sent, false otherwise.
	 */
	public function is_email_sent( int $customer_id ): bool {
		return (bool) get_user_meta( $customer_id, self::META_KEY_EMAIL_SENT, true );
	}

	/**
	 * Get the date when coupon email was sent.
	 *
	 * @param int $customer_id User ID.
	 * @return int|false Timestamp or false if not sent.
	 */
	public function get_email_sent_date( int $customer_id ) {
		$timestamp = get_user_meta( $customer_id, self::META_KEY_EMAIL_SENT, true );
		return $timestamp ? (int) $timestamp : false;
	}

	/**
	 * Reset all customer tracking data.
	 *
	 * @param int $customer_id User ID.
	 * @return bool Always returns true.
	 */
	public function reset_customer_data( int $customer_id ): bool {
		delete_user_meta( $customer_id, self::META_KEY_CLICKED );
		delete_user_meta( $customer_id, self::META_KEY_CLICKED . '_timestamp' );
		delete_user_meta( $customer_id, self::META_KEY_COUPON );
		delete_user_meta( $customer_id, self::META_KEY_EMAIL_SENT );

		return true;
	}
}
