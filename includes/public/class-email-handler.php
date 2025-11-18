<?php

declare(strict_types=1);

namespace GRI\Public_Module;

use WC_Order;
use WC_Email;

/**
 * Email handler
 * 
 * Handles email customization and coupon email sending.
 *
 * @package    Google_Review_Incentive
 * @author     Ahmad Wael
 * @since      1.0.0
 */
class Email_Handler
{

    public function add_review_link(WC_Order $order, bool $sent_to_admin, bool $plain_text, WC_Email $email): void
    {
        if ($email->id !== 'customer_completed_order' || $sent_to_admin) {
            return;
        }

        $customer_id = $order->get_customer_id();
        if ($customer_id && get_user_meta($customer_id, '_gri_review_link_clicked', true)) {
            return;
        }

        $link_text = get_option('gri_link_text', __('Share your experience on Google', 'google-review-incentive'));
        $review_url = $this->generate_review_link($order);

        if (! $review_url) {
            return;
        }

        if ($plain_text) {
            echo "\n\n" . esc_html($link_text) . ": " . esc_url($review_url) . "\n\n";
        } else {
?>
            <div style="margin-bottom: 20px;">
                <p style="margin: 0; font-size: 14px;">
                    <?php echo esc_html__('We hope you enjoyed your purchase!', 'google-review-incentive'); ?>
                </p>
                <p style="margin: 10px 0 0 0;">
                    <a href="<?php echo esc_url($review_url); ?>">
                        <?php echo esc_html($link_text); ?>
                    </a>
                </p>
            </div>
        <?php
        }
    }

    private function generate_review_link(WC_Order $order)
    {
        $place_id = get_option('gri_google_place_id', '');
        if (empty($place_id)) {
            return false;
        }

        $order_id = $order->get_id();
        $customer_email = $order->get_billing_email();

        if (empty($customer_email)) {
            return false;
        }

        // Generate a secure token using order ID, email, and a secret
        $token = $this->generate_review_token($order_id, $customer_email);

        // Store the token and email as order meta for validation
        $order->update_meta_data('_gri_review_token', $token);
        $order->update_meta_data('_gri_customer_email', $customer_email);
        $order->save();

        $tracking_url = add_query_arg(
            [
                'gri_action' => 'review_click',
                'order_id' => $order_id,
                'token' => $token,
            ],
            home_url('/')
        );

        return $tracking_url;
    }

    /**
     * Generate a secure token for review link validation
     *
     * @param int    $order_id Order ID
     * @param string $email    Customer email
     * @return string Secure hash token
     */
    private function generate_review_token(int $order_id, string $email): string
    {
        $secret = wp_salt('nonce');
        return hash_hmac('sha256', $order_id . '|' . $email, $secret);
    }

    public function send_coupon_email(string $customer_email, string $coupon_code): void
    {
        if (empty($customer_email) || ! is_email($customer_email)) {
            return;
        }

        $to = $customer_email;
        $subject = get_option('gri_email_subject', __('Thank you for your review! Here\'s your reward', 'google-review-incentive'));
        $content = get_option('gri_email_content', $this->get_default_email_content());
        $content = str_replace('{coupon_code}', $coupon_code, $content);

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>',
        ];

        $sent = wp_mail($to, $subject, $this->format_email_html($content), $headers);

        if ($sent) {
            // Store sent timestamp for registered users
            $user = get_user_by('email', $customer_email);
            if ($user) {
                update_user_meta($user->ID, '_gri_coupon_sent_date', current_time('timestamp'));
            }

            if (function_exists('wc_get_logger')) {
                $logger = wc_get_logger();
                $logger->info(sprintf('Coupon email sent to %s with coupon code: %s', $customer_email, $coupon_code), ['source' => 'google-review-incentive']);
            }
        }
    }

    private function format_email_html(string $content): string
    {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
        </head>

        <body style="margin: 0; padding: 0; background-color: #f7f7f7; font-family: Arial, sans-serif;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f7f7f7;">
                <tr>
                    <td align="center" style="padding: 40px 0;">
                        <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                            <tr>
                                <td style="padding: 40px;">
                                    <?php echo wp_kses_post(wpautop($content)); ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>

        </html>
<?php
        return ob_get_clean();
    }

    private function get_default_email_content(): string
    {
        return sprintf(__('Thank you for taking the time to share your experience!%sAs a token of our appreciation, here is your exclusive coupon code: <strong>{coupon_code}</strong>%sUse this code on your next purchase to receive your discount.%sBest regards,', 'google-review-incentive'), "\n\n", "\n\n", "\n\n");
    }
}
