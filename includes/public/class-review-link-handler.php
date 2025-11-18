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
class Review_Link_Handler
{

    private $customer_tracker;
    private $coupon_generator;

    public function __construct()
    {
        $this->customer_tracker = new Customer_Tracker();
        $this->coupon_generator = new Coupon_Generator();
    }

    public function handle_review_link_click(): void
    {
        if (! isset($_GET['gri_action']) || $_GET['gri_action'] !== 'review_click') {
            return;
        }

        $order_id = isset($_GET['order_id']) ? absint($_GET['order_id']) : 0;
        $token = isset($_GET['token']) ? sanitize_text_field($_GET['token']) : '';

        if (! $order_id || empty($token)) {
            wp_die(esc_html__('Invalid review link.', 'google-review-incentive'));
        }

        $order = wc_get_order($order_id);
        if (! $order) {
            wp_die(esc_html__('Invalid order.', 'google-review-incentive'));
        }

        // Validate the token
        $stored_token = $order->get_meta('_gri_review_token', true);
        $customer_email = $order->get_meta('_gri_customer_email', true);

        if (empty($stored_token) || empty($customer_email) || ! hash_equals($stored_token, $token)) {
            wp_die(esc_html__('Invalid or expired review link.', 'google-review-incentive'));
        }

        // Check if this email has already claimed a coupon (works for both registered and guest)
        if ($this->customer_tracker->has_email_claimed_coupon($customer_email)) {
            $this->redirect_to_google_reviews();
            return;
        }

        // Get customer ID from email (for registered users)
        $customer_id = 0;
        $user = get_user_by('email', $customer_email);
        if ($user) {
            $customer_id = $user->ID;
        }

        // Track the click for registered users
        if ($customer_id) {
            $this->customer_tracker->track_review_link_click($customer_id);
        }

        if (get_option('gri_enable_coupon', true)) {
            $coupon_code = $this->coupon_generator->generate_coupon($customer_email, $order);

            if ($coupon_code) {
                // Track email claim for guests (registered users already tracked above)
                if (! $customer_id) {
                    $this->customer_tracker->track_email_claim($customer_email, $coupon_code);
                }

                $this->schedule_coupon_email($customer_email, $coupon_code);
            }
        }

        $this->redirect_to_google_reviews();
    }

    private function schedule_coupon_email(string $customer_email, string $coupon_code): void
    {
        $delay_minutes = absint(get_option('gri_email_delay', 60));
        $schedule_time = time() + ($delay_minutes * MINUTE_IN_SECONDS);

        if (function_exists('as_schedule_single_action')) {
            as_schedule_single_action(
                $schedule_time,
                'gri_send_coupon_email',
                [$customer_email, $coupon_code],
                'google-review-incentive'
            );

            if (function_exists('wc_get_logger')) {
                $logger = wc_get_logger();
                $logger->info(sprintf('Scheduled coupon email for %s to be sent in %d minutes', $customer_email, $delay_minutes), ['source' => 'google-review-incentive']);
            }
        }
    }

    private function redirect_to_google_reviews(): void
    {
        $place_id = get_option('gri_google_place_id', '');

        if (empty($place_id)) {
            wp_die(esc_html__('Google Place ID is not configured.', 'google-review-incentive'));
        }

        $google_review_url = sprintf('https://search.google.com/local/writereview?placeid=%s', urlencode($place_id));

        wp_redirect($google_review_url);
        exit;
    }
}
