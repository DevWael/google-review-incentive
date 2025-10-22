<?php
declare(strict_types=1);

namespace GRI\Admin;

/**
 * Admin settings handler
 */
class Admin_Settings {

    private const OPTION_GROUP = 'gri_settings_group';
    private const PAGE_SLUG = 'google-review-incentive';

    public function register_settings(): void {
        register_setting( self::OPTION_GROUP, 'gri_enable_coupon', ['type' => 'boolean', 'default' => true, 'sanitize_callback' => 'rest_sanitize_boolean'] );
        register_setting( self::OPTION_GROUP, 'gri_coupon_type', ['type' => 'string', 'default' => 'percent', 'sanitize_callback' => [ $this, 'sanitize_coupon_type' ]] );
        register_setting( self::OPTION_GROUP, 'gri_coupon_amount', ['type' => 'number', 'default' => 10, 'sanitize_callback' => [ $this, 'sanitize_coupon_amount' ]] );
        register_setting( self::OPTION_GROUP, 'gri_coupon_validity', ['type' => 'integer', 'default' => 30, 'sanitize_callback' => 'absint'] );
        register_setting( self::OPTION_GROUP, 'gri_email_delay', ['type' => 'integer', 'default' => 60, 'sanitize_callback' => 'absint'] );
        register_setting( self::OPTION_GROUP, 'gri_link_text', ['type' => 'string', 'default' => __( 'Share your experience on Google', 'google-review-incentive' ), 'sanitize_callback' => 'sanitize_text_field'] );
        register_setting( self::OPTION_GROUP, 'gri_google_place_id', ['type' => 'string', 'default' => '', 'sanitize_callback' => 'sanitize_text_field'] );
        register_setting( self::OPTION_GROUP, 'gri_email_subject', ['type' => 'string', 'default' => __( 'Thank you for your review! Here\'s your reward', 'google-review-incentive' ), 'sanitize_callback' => 'sanitize_text_field'] );
        register_setting( self::OPTION_GROUP, 'gri_email_content', ['type' => 'string', 'default' => $this->get_default_email_content(), 'sanitize_callback' => 'wp_kses_post'] );

        add_settings_section( 'gri_general_section', __( 'General Settings', 'google-review-incentive' ), [ $this, 'render_general_section' ], self::PAGE_SLUG );
        add_settings_section( 'gri_coupon_section', __( 'Coupon Settings', 'google-review-incentive' ), [ $this, 'render_coupon_section' ], self::PAGE_SLUG );
        add_settings_section( 'gri_email_section', __( 'Email Settings', 'google-review-incentive' ), [ $this, 'render_email_section' ], self::PAGE_SLUG );

        $this->add_general_fields();
        $this->add_coupon_fields();
        $this->add_email_fields();
    }

    private function add_general_fields(): void {
        add_settings_field( 'gri_enable_coupon', __( 'Enable Coupon Generation', 'google-review-incentive' ), [ $this, 'render_checkbox_field' ], self::PAGE_SLUG, 'gri_general_section', ['label_for' => 'gri_enable_coupon', 'description' => __( 'Enable automatic coupon generation for customers who click the review link.', 'google-review-incentive' )] );
        add_settings_field( 'gri_google_place_id', __( 'Google Place ID', 'google-review-incentive' ), [ $this, 'render_text_field' ], self::PAGE_SLUG, 'gri_general_section', ['label_for' => 'gri_google_place_id', 'description' => __( 'Your Google My Business Place ID. Required for the review link to work.', 'google-review-incentive' ), 'placeholder' => 'ChIJN1t_tDeuEmsRUsoyG83frY4'] );
        add_settings_field( 'gri_link_text', __( 'Review Link Text', 'google-review-incentive' ), [ $this, 'render_text_field' ], self::PAGE_SLUG, 'gri_general_section', ['label_for' => 'gri_link_text', 'description' => __( 'The text displayed for the review link in the order completion email.', 'google-review-incentive' )] );
    }

    private function add_coupon_fields(): void {
        add_settings_field( 'gri_coupon_type', __( 'Coupon Type', 'google-review-incentive' ), [ $this, 'render_select_field' ], self::PAGE_SLUG, 'gri_coupon_section', ['label_for' => 'gri_coupon_type', 'options' => ['percent' => __( 'Percentage Discount', 'google-review-incentive' ), 'fixed_cart' => __( 'Fixed Cart Discount', 'google-review-incentive' ), 'fixed_product' => __( 'Fixed Product Discount', 'google-review-incentive' )], 'description' => __( 'Select the type of discount for the generated coupon.', 'google-review-incentive' )] );
        add_settings_field( 'gri_coupon_amount', __( 'Coupon Amount', 'google-review-incentive' ), [ $this, 'render_number_field' ], self::PAGE_SLUG, 'gri_coupon_section', ['label_for' => 'gri_coupon_amount', 'description' => __( 'The discount amount (percentage or fixed value based on coupon type).', 'google-review-incentive' ), 'min' => 0, 'step' => 0.01] );
        add_settings_field( 'gri_coupon_validity', __( 'Coupon Validity (Days)', 'google-review-incentive' ), [ $this, 'render_number_field' ], self::PAGE_SLUG, 'gri_coupon_section', ['label_for' => 'gri_coupon_validity', 'description' => __( 'Number of days the coupon remains valid after generation.', 'google-review-incentive' ), 'min' => 1, 'step' => 1] );
    }

    private function add_email_fields(): void {
        add_settings_field( 'gri_email_delay', __( 'Email Delay (Minutes)', 'google-review-incentive' ), [ $this, 'render_number_field' ], self::PAGE_SLUG, 'gri_email_section', ['label_for' => 'gri_email_delay', 'description' => __( 'Time delay before sending the coupon email after the customer clicks the review link.', 'google-review-incentive' ), 'min' => 1, 'step' => 1] );
        add_settings_field( 'gri_email_subject', __( 'Email Subject', 'google-review-incentive' ), [ $this, 'render_text_field' ], self::PAGE_SLUG, 'gri_email_section', ['label_for' => 'gri_email_subject', 'description' => __( 'The subject line for the coupon email.', 'google-review-incentive' )] );
        add_settings_field( 'gri_email_content', __( 'Email Content', 'google-review-incentive' ), [ $this, 'render_textarea_field' ], self::PAGE_SLUG, 'gri_email_section', ['label_for' => 'gri_email_content', 'description' => __( 'The content of the coupon email. Use {coupon_code} placeholder for the coupon code.', 'google-review-incentive' ), 'rows' => 10] );
    }

    public function render_general_section(): void { echo '<p>' . esc_html__( 'Configure the general settings for the Google Review Incentive plugin.', 'google-review-incentive' ) . '</p>'; }
    public function render_coupon_section(): void { echo '<p>' . esc_html__( 'Configure the coupon settings that will be generated for customers.', 'google-review-incentive' ) . '</p>'; }
    public function render_email_section(): void { echo '<p>' . esc_html__( 'Configure the email settings for the coupon delivery.', 'google-review-incentive' ) . '</p>'; }

    public function render_checkbox_field( array $args ): void {
        $option = get_option( $args['label_for'], true );
        echo '<input type="checkbox" id="' . esc_attr( $args['label_for'] ) . '" name="' . esc_attr( $args['label_for'] ) . '" value="1" ' . checked( $option, true, false ) . '>';
        if ( ! empty( $args['description'] ) ) echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
    }

    public function render_text_field( array $args ): void {
        $option = get_option( $args['label_for'], $args['default'] ?? '' );
        echo '<input type="text" id="' . esc_attr( $args['label_for'] ) . '" name="' . esc_attr( $args['label_for'] ) . '" value="' . esc_attr( $option ) . '" class="regular-text"';
        if ( ! empty( $args['placeholder'] ) ) echo ' placeholder="' . esc_attr( $args['placeholder'] ) . '"';
        echo '>';
        if ( ! empty( $args['description'] ) ) echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
    }

    public function render_number_field( array $args ): void {
        $option = get_option( $args['label_for'], $args['default'] ?? 0 );
        echo '<input type="number" id="' . esc_attr( $args['label_for'] ) . '" name="' . esc_attr( $args['label_for'] ) . '" value="' . esc_attr( $option ) . '" min="' . esc_attr( $args['min'] ?? 0 ) . '" step="' . esc_attr( $args['step'] ?? 1 ) . '" class="small-text">';
        if ( ! empty( $args['description'] ) ) echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
    }

    public function render_select_field( array $args ): void {
        $option = get_option( $args['label_for'], $args['default'] ?? '' );
        echo '<select id="' . esc_attr( $args['label_for'] ) . '" name="' . esc_attr( $args['label_for'] ) . '">';
        foreach ( $args['options'] as $value => $label ) {
            echo '<option value="' . esc_attr( $value ) . '" ' . selected( $option, $value, false ) . '>' . esc_html( $label ) . '</option>';
        }
        echo '</select>';
        if ( ! empty( $args['description'] ) ) echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
    }

    public function render_textarea_field( array $args ): void {
        $option = get_option( $args['label_for'], $args['default'] ?? '' );
        echo '<textarea id="' . esc_attr( $args['label_for'] ) . '" name="' . esc_attr( $args['label_for'] ) . '" rows="' . esc_attr( $args['rows'] ?? 5 ) . '" class="large-text">' . esc_textarea( $option ) . '</textarea>';
        if ( ! empty( $args['description'] ) ) echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
    }

    public function sanitize_coupon_type( string $value ): string {
        $allowed = [ 'percent', 'fixed_cart', 'fixed_product' ];
        return in_array( $value, $allowed, true ) ? $value : 'percent';
    }

    public function sanitize_coupon_amount( $value ): float {
        return max( 0, (float) $value );
    }

    private function get_default_email_content(): string {
        return sprintf( __( 'Thank you for taking the time to share your experience!%sAs a token of our appreciation, here is your exclusive coupon code: <strong>{coupon_code}</strong>%sUse this code on your next purchase to receive your discount.%sBest regards,', 'google-review-incentive' ), "\n\n", "\n\n", "\n\n" );
    }
}
