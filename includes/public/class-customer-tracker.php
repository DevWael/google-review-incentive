<?php
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

    private const META_KEY_CLICKED = '_gri_review_link_clicked';
    private const META_KEY_COUPON = '_gri_coupon_code';
    private const META_KEY_EMAIL_SENT = '_gri_coupon_sent_date';

    public function has_clicked_review_link( int $customer_id ): bool {
        return (bool) get_user_meta( $customer_id, self::META_KEY_CLICKED, true );
    }

    public function track_review_link_click( int $customer_id ): bool {
        if ( $this->has_clicked_review_link( $customer_id ) ) {
            return false;
        }

        $result = update_user_meta( $customer_id, self::META_KEY_CLICKED, true );
        update_user_meta( $customer_id, self::META_KEY_CLICKED . '_timestamp', current_time( 'timestamp' ) );

        return (bool) $result;
    }

    public function get_customer_coupon_code( int $customer_id ) {
        return get_user_meta( $customer_id, self::META_KEY_COUPON, true );
    }

    public function is_email_sent( int $customer_id ): bool {
        return (bool) get_user_meta( $customer_id, self::META_KEY_EMAIL_SENT, true );
    }

    public function get_email_sent_date( int $customer_id ) {
        $timestamp = get_user_meta( $customer_id, self::META_KEY_EMAIL_SENT, true );
        return $timestamp ? (int) $timestamp : false;
    }

    public function reset_customer_data( int $customer_id ): bool {
        delete_user_meta( $customer_id, self::META_KEY_CLICKED );
        delete_user_meta( $customer_id, self::META_KEY_CLICKED . '_timestamp' );
        delete_user_meta( $customer_id, self::META_KEY_COUPON );
        delete_user_meta( $customer_id, self::META_KEY_EMAIL_SENT );

        return true;
    }
}
