# Changelog

## [Unreleased] - Security Enhancement

### Changed
- **BREAKING CHANGE**: Migrated from customer ID-based URLs to email-based authentication with secure tokens
- Review links now use secure HMAC tokens instead of predictable customer IDs
- All email sending functions now accept customer email addresses instead of customer IDs
- Coupon generation now works with email addresses, supporting both registered users and guest checkouts

### Security
- **Fixed**: Prevented URL manipulation attacks by removing customer IDs from review links
- **Added**: Secure token validation using HMAC-SHA256
- **Added**: Token storage as order meta for validation
- **Improved**: Email-based identification works for both registered users and guests

### Technical Details

#### Modified Files
1. **class-email-handler.php**
   - Added `generate_review_token()` method for secure token generation
   - Updated `generate_review_link()` to use tokens instead of customer_id
   - Modified `send_coupon_email()` to accept email instead of customer_id
   - Tokens are stored using WooCommerce order meta methods (`update_meta_data()`, `get_meta()`)
   - Uses `$order->save()` to persist meta data changes

2. **class-review-link-handler.php**
   - Updated `handle_review_link_click()` to validate tokens instead of customer_id
   - Added secure token validation using `hash_equals()` to prevent timing attacks
   - Modified `schedule_coupon_email()` to accept email instead of customer_id
   - Now supports both registered users and guest checkouts
   - Uses WooCommerce order meta methods (`get_meta()`) for retrieving stored data

3. **class-coupon-generator.php**
   - Updated `generate_coupon()` to accept email instead of customer_id
   - Modified `generate_unique_coupon_code()` to use email for code generation
   - Added email validation using `is_email()`
   - Coupon metadata is stored only for registered users

4. **class-customer-tracker.php**
   - Added `has_email_claimed_coupon()` to check if an email has already claimed a coupon
   - Added `track_email_claim()` to track guest customer claims
   - Uses WordPress options table with `autoload = false` for performance
   - Stores guest tracking data in `gri_guest_review_clicks` option
   - Normalizes emails (lowercase, trimmed) for consistent tracking

### Migration Notes
- Existing review links with customer_id parameter will no longer work
- New orders will automatically receive secure token-based links
- Guest checkout customers can now receive coupons (previously required registered account)

### Benefits
1. **Better Security**: Tokens cannot be guessed or manipulated
2. **Guest Checkout Support**: Now works with guest customers (previously required registration)
3. **Privacy**: Customer IDs no longer exposed in URLs
4. **Flexibility**: Email-based system is more scalable
5. **HPOS Compatible**: Uses WooCommerce order meta methods, ensuring compatibility with High-Performance Order Storage (HPOS) for future enhancements
