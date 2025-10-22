# Installation & Setup Guide

## Google Review Incentive for WooCommerce

### Prerequisites

Before installing, ensure you have:
- ✅ WordPress 5.8 or higher
- ✅ WooCommerce 5.0 or higher installed and activated
- ✅ PHP 7.4 or higher
- ✅ A Google My Business account with a verified business

---

## Step 1: Install the Plugin

### Method 1: Manual Upload

1. Download the plugin ZIP file
2. Go to WordPress Admin → **Plugins → Add New**
3. Click **Upload Plugin**
4. Choose the ZIP file and click **Install Now**
5. Click **Activate Plugin**

### Method 2: FTP Upload

1. Extract the plugin ZIP file
2. Upload the `google-review-incentive` folder to `/wp-content/plugins/`
3. Go to WordPress Admin → **Plugins**
4. Find "Google Review Incentive for WooCommerce" and click **Activate**

---

## Step 2: Get Your Google Place ID

Your Google Place ID is required for the plugin to work.

### Finding Your Place ID:

1. Go to [Google Place ID Finder](https://developers.google.com/maps/documentation/javascript/examples/places-placeid-finder)
2. Search for your business name
3. Click on your business in the results
4. Copy the **Place ID** (starts with "ChIJ...")

**Alternative Method:**

1. Search your business on Google Maps
2. Click "Share" button
3. Click "Embed a map"
4. Copy the Place ID from the iframe code

**Example Place ID:** `ChIJN1t_tDeuEmsRUsoyG83frY4`

---

## Step 3: Configure Plugin Settings

1. Go to **WooCommerce → Review Incentive** in WordPress admin
2. Configure the following settings:

### General Settings

**Enable Coupon Generation:**
- ✅ Check to enable automatic coupon generation
- ⬜ Uncheck to only track clicks without coupons

**Google Place ID:** (Required)
- Paste your Place ID from Step 2
- Example: `ChIJN1t_tDeuEmsRUsoyG83frY4`

**Review Link Text:**
- Customize the text shown in emails
- Default: "Share your experience on Google"
- Example: "Leave us a review and get rewarded!"

### Coupon Settings

**Coupon Type:**
- **Percentage Discount** - e.g., 10% off
- **Fixed Cart Discount** - e.g., $5 off cart
- **Fixed Product Discount** - e.g., $2 off per product

**Coupon Amount:**
- Enter the discount value
- For percentage: Enter 10 for 10%
- For fixed: Enter 5 for $5

**Coupon Validity (Days):**
- How long the coupon remains valid
- Default: 30 days
- Recommended: 30-60 days

### Email Settings

**Email Delay (Minutes):**
- Time before sending coupon email
- Default: 60 minutes
- Recommended: 30-120 minutes
- Gives customer time to write review

**Email Subject:**
- Subject line for coupon email
- Default: "Thank you for your review! Here's your reward"
- Can include emojis: "Thank you! 🎁 Here's your reward"

**Email Content:**
- Customize the email message
- Use `{coupon_code}` placeholder for the actual code
- Supports HTML formatting

**Default Content:**
```
Thank you for taking the time to share your experience!

As a token of our appreciation, here is your exclusive coupon code: {coupon_code}

Use this code on your next purchase to receive your discount.

Best regards,
```

4. Click **Save Settings**

---

## Step 4: Test the Plugin

### Create a Test Order

1. Create a test order in WooCommerce
2. Set order status to **Completed**
3. Check the order completion email

### Verify Review Link

The email should contain:
- Custom review link button/text
- Link format: `yoursite.com/?gri_action=review_click&order_id=...`

### Test the Click Flow

1. Click the review link in the email
2. You should be redirected to Google Reviews
3. After the delay period (60 min by default), check for coupon email

### Check Action Scheduler

1. Go to **WooCommerce → Status → Scheduled Actions**
2. Search for: `gri_send_coupon_email`
3. Verify scheduled actions appear

### Verify Coupon Generation

1. Go to **WooCommerce → Coupons**
2. Look for coupons starting with `REVIEW-`
3. Check coupon settings:
   - Usage limit: 1
   - Email restrictions: Customer's email
   - Expiry date: Based on your settings

---

## Step 5: Monitor Performance

### Check Logs

Go to **WooCommerce → Status → Logs** and select:
- `google-review-incentive-[date].log`

Logs include:
- Coupon generation events
- Email sending confirmation
- Scheduled action creation
- Any errors or warnings

### Track Customer Usage

Check customer's meta data:
- `_gri_review_link_clicked` - Did they click?
- `_gri_coupon_code` - Their coupon code
- `_gri_coupon_sent_date` - When email was sent

---

## File Structure Setup

After installation, verify this structure:

```
wp-content/plugins/google-review-incentive/
│
├── google-review-incentive.php
├── README.md
├── uninstall.php
│
├── includes/
│   ├── class-google-review-incentive.php
│   ├── class-activator.php
│   ├── class-deactivator.php
│   │
│   ├── admin/
│   │   ├── class-admin-menu.php
│   │   └── class-admin-settings.php
│   │
│   └── public/
│       ├── class-email-handler.php
│       ├── class-review-link-handler.php
│       ├── class-coupon-generator.php
│       └── class-customer-tracker.php
│
└── assets/
    ├── css/
    │   └── admin-styles.css (optional)
    └── js/
        └── admin-scripts.js (optional)
```

---

## Common Issues & Solutions

### Issue: Review link not appearing in email

**Solution:**
- Verify plugin is activated
- Check Google Place ID is entered
- Ensure order status is "Completed"
- Check email template isn't overridden by theme

### Issue: Customer redirects but no coupon email

**Solution:**
- Check Action Scheduler is working
- Verify WP-Cron is enabled
- Check email delay setting
- Look at WooCommerce logs for errors

### Issue: Coupon not applying at checkout

**Solution:**
- Verify customer email matches order email
- Check coupon hasn't been used already
- Ensure coupon hasn't expired
- Check cart meets any minimum requirements

### Issue: Customer can click multiple times

**Solution:**
- This is intentional - prevents error messages
- First click generates coupon
- Subsequent clicks only redirect
- User meta prevents multiple coupon generation

---

## Advanced Configuration

### Disable WP-Cron (Optional)

For high-traffic sites, disable WP-Cron and use system cron:

**In wp-config.php:**
```php
define('DISABLE_WP_CRON', true);
```

**Add to system crontab:**
```bash
*/5 * * * * wget -q -O - https://yoursite.com/wp-cron.php?doing_wp_cron >/dev/null 2>&1
```

### Custom Email Template (Optional)

Create custom template in your theme:
```
your-theme/woocommerce/emails/review-incentive.php
```

### Enable Debug Logging

**In wp-config.php:**
```php
define('WC_LOG_THRESHOLD', 'debug');
```

---

## Security Checklist

✅ Nonce verification on all links  
✅ User meta prevents duplicates  
✅ Email-restricted coupons  
✅ One-time usage limits  
✅ Input sanitization  
✅ SQL injection prevention  
✅ XSS protection  

---

## Performance Optimization

### For Large Stores (1000+ orders/month):

1. **Enable Object Caching**
   - Install Redis or Memcached
   - Improves user meta queries

2. **Optimize Action Scheduler**
   - Keep queue clean
   - Monitor pending actions
   - Consider batch processing

3. **Email Rate Limiting**
   - Increase email delay to spread load
   - Use transactional email service (SendGrid, Mailgun)

---

## Need Help?

**Support:** Visit [www.bbioon.com](https://www.bbioon.com)  
**Documentation:** Check README.md in plugin folder  
**Logs:** WooCommerce → Status → Logs  
**Scheduled Actions:** WooCommerce → Status → Scheduled Actions

---

**Version:** 1.0.0  
**Last Updated:** October 2025  
**Author:** Ahmad Wael
