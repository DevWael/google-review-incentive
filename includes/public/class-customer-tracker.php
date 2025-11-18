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
class Customer_Tracker
{

    private const META_KEY_CLICKED = '_gri_review_link_clicked';
    private const META_KEY_COUPON = '_gri_coupon_code';
    private const META_KEY_EMAIL_SENT = '_gri_coupon_sent_date';
    private const OPTION_GUEST_CLICKS = 'gri_guest_review_clicks';

    /**
     * Check if a customer has clicked the review link (registered users)
     *
     * @param int $customer_id User ID
     * @return bool
     */
    public function has_clicked_review_link(int $customer_id): bool
    {
        return (bool) get_user_meta($customer_id, self::META_KEY_CLICKED, true);
    }

    /**
     * Check if an email has already claimed a coupon (for guests and registered users)
     *
     * @param string $email Customer email
     * @return bool
     */
    public function has_email_claimed_coupon(string $email): bool
    {
        if (empty($email)) {
            return false;
        }

        // Normalize email
        $email = strtolower(trim($email));

        // Check if registered user
        $user = get_user_by('email', $email);
        if ($user) {
            return $this->has_clicked_review_link($user->ID);
        }

        // Check guest tracking
        $guest_clicks = get_option(self::OPTION_GUEST_CLICKS, []);
        return isset($guest_clicks[$email]);
    }

    /**
     * Track email as having claimed a coupon (for guests)
     *
     * @param string $email Customer email
     * @param string $coupon_code Generated coupon code
     * @return bool
     */
    public function track_email_claim(string $email, string $coupon_code = ''): bool
    {
        if (empty($email)) {
            return false;
        }

        // Normalize email
        $email = strtolower(trim($email));

        // Don't track if already claimed
        if ($this->has_email_claimed_coupon($email)) {
            return false;
        }

        // Get existing data
        $guest_clicks = get_option(self::OPTION_GUEST_CLICKS, []);

        // Add new entry
        $guest_clicks[$email] = [
            'timestamp' => current_time('timestamp'),
            'coupon_code' => $coupon_code,
        ];

        // Update option with autoload = false
        return update_option(self::OPTION_GUEST_CLICKS, $guest_clicks, false);
    }

    public function track_review_link_click(int $customer_id): bool
    {
        if ($this->has_clicked_review_link($customer_id)) {
            return false;
        }

        $result = update_user_meta($customer_id, self::META_KEY_CLICKED, true);
        update_user_meta($customer_id, self::META_KEY_CLICKED . '_timestamp', current_time('timestamp'));

        return (bool) $result;
    }

    public function get_customer_coupon_code(int $customer_id)
    {
        return get_user_meta($customer_id, self::META_KEY_COUPON, true);
    }

    public function is_email_sent(int $customer_id): bool
    {
        return (bool) get_user_meta($customer_id, self::META_KEY_EMAIL_SENT, true);
    }

    public function get_email_sent_date(int $customer_id)
    {
        $timestamp = get_user_meta($customer_id, self::META_KEY_EMAIL_SENT, true);
        return $timestamp ? (int) $timestamp : false;
    }

    public function reset_customer_data(int $customer_id): bool
    {
        delete_user_meta($customer_id, self::META_KEY_CLICKED);
        delete_user_meta($customer_id, self::META_KEY_CLICKED . '_timestamp');
        delete_user_meta($customer_id, self::META_KEY_COUPON);
        delete_user_meta($customer_id, self::META_KEY_EMAIL_SENT);

        return true;
    }
}
