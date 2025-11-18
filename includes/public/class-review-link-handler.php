<?php
declare(strict_types=1);

namespace GRI\Public_Module;

/**
 * Review link handler
 * 
 * Handles review link clicks, tracks customers, generates coupons.
 *
 * @package    Google_Review_Incentive
 * @author     Ahmad Wael
 * @since      1.0.0
 */
class Review_Link_Handler {

    private $customer_tracker;
    private $coupon_generator;

    public function __construct() {
        $this->customer_tracker = new Customer_Tracker();
        $this->coupon_generator = new Coupon_Generator();
    }

    public function handle_review_link_click(): void {
        if ( ! isset( $_GET['gri_action'] ) || $_GET['gri_action'] !== 'review_click' ) {
            return;
        }

        $order_id = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0;
        $customer_id = isset( $_GET['customer_id'] ) ? absint( $_GET['customer_id'] ) : 0;

        if ( ! $order_id || ! $customer_id ) {
            wp_die( esc_html__( 'Invalid review link.', 'google-review-incentive' ) );
        }

        if ( $this->customer_tracker->has_clicked_review_link( $customer_id ) ) {
            $this->redirect_to_google_reviews();
            return;
        }

        $order = wc_get_order( $order_id );
        if ( ! $order || $order->get_customer_id() !== $customer_id ) {
            wp_die( esc_html__( 'Invalid order.', 'google-review-incentive' ) );
        }

        $this->customer_tracker->track_review_link_click( $customer_id );

        if ( get_option( 'gri_enable_coupon', true ) ) {
            $coupon_code = $this->coupon_generator->generate_coupon( $customer_id, $order );

            if ( $coupon_code ) {
                $this->schedule_coupon_email( $customer_id, $coupon_code );
            }
        }

        $this->redirect_to_google_reviews();
    }

    private function schedule_coupon_email( int $customer_id, string $coupon_code ): void {
        $delay_minutes = absint( get_option( 'gri_email_delay', 60 ) );
        $schedule_time = time() + ( $delay_minutes * MINUTE_IN_SECONDS );

        if ( function_exists( 'as_schedule_single_action' ) ) {
            as_schedule_single_action(
                $schedule_time,
                'gri_send_coupon_email',
                [ $customer_id, $coupon_code ],
                'google-review-incentive'
            );

            if ( function_exists( 'wc_get_logger' ) ) {
                $logger = wc_get_logger();
                $logger->info( sprintf( 'Scheduled coupon email for customer #%d to be sent in %d minutes', $customer_id, $delay_minutes ), [ 'source' => 'google-review-incentive' ] );
            }
        }
    }

    private function redirect_to_google_reviews(): void {
        $place_id = get_option( 'gri_google_place_id', '' );

        if ( empty( $place_id ) ) {
            wp_die( esc_html__( 'Google Place ID is not configured.', 'google-review-incentive' ) );
        }

        $google_review_url = sprintf( 'https://search.google.com/local/writereview?placeid=%s', urlencode( $place_id ) );

        wp_redirect( $google_review_url );
        exit;
    }
}
