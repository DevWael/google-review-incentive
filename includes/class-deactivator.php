<?php
declare(strict_types=1);

namespace GRI;

/**
 * Plugin deactivator
 *
 * @package    Google_Review_Incentive
 * @author     Ahmad Wael
 * @since      1.0.0
 */
class Deactivator {

    public static function deactivate(): void {
        self::clear_scheduled_actions();
        flush_rewrite_rules();
    }

    private static function clear_scheduled_actions(): void {
        if ( function_exists( 'as_unschedule_all_actions' ) ) {
            as_unschedule_all_actions( 'gri_send_coupon_email', [], 'google-review-incentive' );
        }
    }
}
