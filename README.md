# Google Review Incentive for WooCommerce

**Version:** 1.0.0  
**Author:** Ahmad Wael  
**Website:** [www.bbioon.com](https://www.bbioon.com)  
**Requires:** WordPress 5.8+, WooCommerce 5.0+, PHP 7.4+

## Description

A professional WordPress/WooCommerce plugin that encourages customers to leave Google reviews by offering one-time coupon codes after order completion.

## Features

- ✅ Custom review link in order completion emails
- ✅ One-time incentive system (prevents duplicate usage)
- ✅ Automatic coupon generation with customer restrictions
- ✅ Scheduled email delivery using WooCommerce Action Scheduler
- ✅ Secure nonce-protected links
- ✅ Comprehensive admin settings panel
- ✅ Full WooCommerce integration
- ✅ WordPress coding standards compliant
- ✅ PSR-2 compliant code
- ✅ Comprehensive logging

## Installation

1. Upload the `google-review-incentive` folder to `/wp-content/plugins/`
2. Activate the plugin through the WordPress admin
3. Go to **WooCommerce → Review Incentive**
4. Configure your settings:
   - Enter your Google Place ID (required)
   - Set coupon type and amount
   - Customize email content
   - Adjust timing settings

## Finding Your Google Place ID

1. Search for your business on Google Maps
2. Click "Share" → "Embed a map"
3. Copy the Place ID from the embed code (starts with "ChIJ")

## File Structure

```
google-review-incentive/
├── google-review-incentive.php          # Main plugin file
├── README.md
├── uninstall.php
├── includes/
│   ├── class-google-review-incentive.php
│   ├── class-activator.php
│   ├── class-deactivator.php
│   ├── admin/
│   │   ├── class-admin-menu.php
│   │   └── class-admin-settings.php
│   └── public/
│       ├── class-email-handler.php
│       ├── class-review-link-handler.php
│       ├── class-coupon-generator.php
│       └── class-customer-tracker.php
└── assets/
    ├── css/
    │   └── admin-styles.css
    └── js/
        └── admin-scripts.js
```

## Workflow

1. **Order Completed** → WooCommerce sends order completion email
2. **Custom Link Added** → Plugin adds review link to email
3. **Customer Clicks** → Link tracked with nonce verification
4. **Coupon Generated** → Unique code with customer restrictions
5. **Email Scheduled** → Action Scheduler queues email (default: 1 hour)
6. **Google Redirect** → Customer redirected to Google Reviews
7. **Email Sent** → Follow-up email with coupon code
8. **One-Time Use** → System prevents duplicate usage

## Settings

### General Settings
- Enable/Disable coupon generation
- Google Place ID
- Review link text

### Coupon Settings
- Coupon type (percentage, fixed cart, fixed product)
- Coupon amount
- Validity period (days)

### Email Settings
- Email delay (minutes)
- Email subject
- Email content (supports {coupon_code} placeholder)

## Security Features

- Nonce verification on all links
- User meta tracking to prevent duplicates
- Email-restricted coupons
- One-time usage limits
- Input sanitization and validation

## Developer Hooks

### Filters

```php
// Customize coupon code format
add_filter( 'gri_coupon_code_format', function( $code, $customer_id ) {
    return 'CUSTOM-' . $code;
}, 10, 2 );

// Modify Google review URL
add_filter( 'gri_google_review_url', function( $url, $place_id ) {
    return add_query_arg( 'utm_source', 'email', $url );
}, 10, 2 );
```

### Actions

```php
// After review link click
add_action( 'gri_after_review_link_click', function( $customer_id, $order_id ) {
    // Custom tracking
}, 10, 2 );

// After coupon generation
add_action( 'gri_after_coupon_generated', function( $coupon_code, $customer_id ) {
    // Custom logic
}, 10, 2 );
```

## Troubleshooting

### Emails Not Sending

Check Action Scheduler: **WooCommerce → Status → Scheduled Actions**

### Review Link Not Working

1. Verify Google Place ID is valid
2. Check link hasn't expired (24 hours)
3. Ensure customer hasn't clicked before

### Coupons Not Applying

1. Verify email matches customer email
2. Check coupon hasn't been used
3. Ensure coupon hasn't expired

## Requirements

- WordPress 5.8 or higher
- WooCommerce 5.0 or higher
- PHP 7.4 or higher

## Changelog

### 1.0.0
- Initial release
- Core functionality
- Admin settings panel
- Email customization
- Coupon generation
- Action Scheduler integration

## License

GPL-2.0+

## Support

For support, please visit [www.bbioon.com](https://www.bbioon.com)

## Credits

Developed by Ahmad Wael  
Following WordPress and WooCommerce coding standards
