/**
 * Google Review Incentive for WooCommerce - Admin Scripts
 * 
 * JavaScript functionality for the plugin settings page.
 * Includes form validation, dynamic field updates, and UX enhancements.
 *
 * @package    Google_Review_Incentive
 * @author     Ahmad Wael <https://www.bbioon.com>
 * @since      1.0.0
 * @version    1.0.3
 */

(function($) {
    'use strict';

    /**
     * Safely get field value - handles all edge cases
     */
    function getFieldValue($field) {
        if (!$field || !$field.length || $field.length === 0) {
            return '';
        }

        try {
            // Get the raw DOM element
            const element = $field[0];

            if (!element) {
                return '';
            }

            // Try to get value directly from DOM element first
            if (element.value !== undefined && element.value !== null) {
                return String(element.value);
            }

            // Fallback to jQuery val() but with additional protection
            if (typeof $field.val === 'function') {
                try {
                    const value = $field.val();
                    if (value === null || value === undefined) {
                        return '';
                    }
                    return String(value);
                } catch (valError) {
                    // jQuery .val() failed, use DOM property
                    return element.value ? String(element.value) : '';
                }
            }

            return '';
        } catch (e) {
            return '';
        }
    }

    /**
     * Safely set field value
     */
    function setFieldValue($field, value) {
        if (!$field || !$field.length) {
            return false;
        }

        try {
            const element = $field[0];
            if (element) {
                element.value = value;
            }
            return true;
        } catch (e) {
            return false;
        }
    }

    /**
     * Main admin object
     */
    const GRI_Admin = {

        /**
         * Initialize admin functionality
         */
        init: function() {
            try {
                this.bindEvents();
                this.initValidation();
                this.initTooltips();
                this.initConditionalFields();
                this.initCopyButtons();
                this.initPreview();
            } catch (e) {
                return;
            }
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            try {
                // Form submission
                $('form').on('submit', this.handleFormSubmit.bind(this));

                // Enable/disable coupon toggle
                const $enableCoupon = $('#gri_enable_coupon');
                if ($enableCoupon.length) {
                    $enableCoupon.on('change', this.toggleCouponFields.bind(this));
                }

                // Coupon type change
                const $couponType = $('#gri_coupon_type');
                if ($couponType.length) {
                    $couponType.on('change', this.updateCouponAmountLabel.bind(this));
                }

                // Email content preview
                const $emailContent = $('#gri_email_content');
                if ($emailContent.length) {
                    $emailContent.on('input', this.updateEmailPreview.bind(this));
                }

                // Google Place ID validation
                const $placeId = $('#gri_google_place_id');
                if ($placeId.length) {
                    $placeId.on('blur', this.validatePlaceId.bind(this));
                }

                // Real-time field validation
                $('input[type="text"], input[type="number"], textarea').each(function() {
                    const $this = $(this);
                    if ($this.length) {
                        $this.on('blur', function() {
                            GRI_Admin.validateField.call(this);
                        });
                    }
                });

                // Unsaved changes warning
                $('input, textarea, select').on('change', this.markFormAsDirty.bind(this));
                $(window).on('beforeunload', this.warnUnsavedChanges.bind(this));

            } catch (e) {
                
            }
        },

        /**
         * Initialize form validation
         */
        initValidation: function() {
            this.markRequiredFields();
            // Skip initial validation to avoid errors on page load
        },

        /**
         * Mark required fields
         */
        markRequiredFields: function() {
            const requiredFields = ['gri_google_place_id'];

            requiredFields.forEach(function(fieldId) {
                const $field = $('#' + fieldId);
                if ($field.length) {
                    $field.closest('tr').addClass('required-field');
                }
            });
        },

        /**
         * Validate individual field
         */
        validateField: function() {
            try {
                const $field = $(this);

                if (!$field || !$field.length) {
                    return true;
                }

                const fieldId = $field.attr('id');
                const fieldValue = getFieldValue($field).trim();

                // Remove previous error messages
                $field.removeClass('error success');
                $field.siblings('.error-message').remove();

                // Skip validation if no field ID
                if (!fieldId) {
                    return true;
                }

                // Google Place ID validation
                if (fieldId === 'gri_google_place_id') {
                    if (!fieldValue) {
                        GRI_Admin.showFieldError($field, 'Google Place ID is required');
                        return false;
                    }

                    if (!fieldValue.startsWith('ChIJ')) {
                        GRI_Admin.showFieldError($field, 'Invalid Place ID format (should start with "ChIJ")');
                        return false;
                    }

                    $field.addClass('success');
                    return true;
                }

                // Email delay validation
                if (fieldId === 'gri_email_delay') {
                    const delay = parseInt(fieldValue);
                    if (isNaN(delay) || delay < 1) {
                        GRI_Admin.showFieldError($field, 'Email delay must be at least 1 minute');
                        return false;
                    }

                    if (delay > 1440) {
                        GRI_Admin.showFieldWarning($field, 'Long delays may reduce effectiveness');
                    }

                    $field.addClass('success');
                    return true;
                }

                // Coupon amount validation
                if (fieldId === 'gri_coupon_amount') {
                    const amount = parseFloat(fieldValue);
                    if (isNaN(amount) || amount <= 0) {
                        GRI_Admin.showFieldError($field, 'Coupon amount must be greater than 0');
                        return false;
                    }

                    const $couponType = $('#gri_coupon_type');
                    const couponType = getFieldValue($couponType);

                    if (couponType === 'percent' && amount > 100) {
                        GRI_Admin.showFieldError($field, 'Percentage cannot exceed 100%');
                        return false;
                    }

                    $field.addClass('success');
                    return true;
                }

                // Coupon validity validation
                if (fieldId === 'gri_coupon_validity') {
                    const validity = parseInt(fieldValue);
                    if (isNaN(validity) || validity < 1) {
                        GRI_Admin.showFieldError($field, 'Validity must be at least 1 day');
                        return false;
                    }

                    $field.addClass('success');
                    return true;
                }

                return true;
            } catch (e) {
                console.error('[GRI] Field validation error:', e);
                return true;
            }
        },

        /**
         * Show field error
         */
        showFieldError: function($field, message) {
            try {
                if ($field && $field.length) {
                    $field.addClass('error');
                    $field.after('<span class="error-message">' + message + '</span>');
                }
            } catch (e) {
                console.error('[GRI] Show error failed:', e);
            }
        },

        /**
         * Show field warning
         */
        showFieldWarning: function($field, message) {
            try {
                if ($field && $field.length) {
                    $field.after('<span class="error-message" style="color: #dba617;">' + message + '</span>');
                }
            } catch (e) {
                console.error('[GRI] Show warning failed:', e);
            }
        },

        /**
         * Validate Google Place ID format
         */
        validatePlaceId: function(e) {
            try {
                const $field = $(this);

                if (!$field || !$field.length) {
                    console.warn('[GRI] Place ID field not found');
                    return;
                }

                // Get field value safely
                let placeId = getFieldValue($field);

                // Skip if empty
                if (!placeId) {
                    return;
                }

                placeId = placeId.trim();

                // Disable field
                $field.prop('disabled', true);

                // Remove existing spinners
                $field.siblings('.spinner').remove();

                // Add new spinner
                $field.after('<span class="spinner is-active" style="float: none; margin: 0 10px;"></span>');

                // Simulate validation
                setTimeout(function() {
                    try {
                        $('.spinner').remove();
                        $field.prop('disabled', false);

                        if (placeId.startsWith('ChIJ')) {
                            GRI_Admin.showNotice('Place ID format is valid', 'success');
                        } else {
                            GRI_Admin.showNotice('Invalid Place ID format. It should start with "ChIJ"', 'error');
                        }
                    } catch (err) {
                        console.error('[GRI] Validation timeout error:', err);
                        $('.spinner').remove();
                        $field.prop('disabled', false);
                    }
                }, 500);
            } catch (e) {
                console.error('[GRI] Place ID validation error:', e);
                $('.spinner').remove();
                try {
                    $(this).prop('disabled', false);
                } catch (err) {
                    // Ignore
                }
            }
        },

        /**
         * Toggle coupon-related fields
         */
        toggleCouponFields: function() {
            try {
                const $this = $(this);
                if (!$this.length) return;

                const isEnabled = $this.is(':checked');
                const $couponFields = $('#gri_coupon_type, #gri_coupon_amount, #gri_coupon_validity').closest('tr');

                if ($couponFields.length) {
                    if (isEnabled) {
                        $couponFields.fadeIn(300);
                    } else {
                        $couponFields.fadeOut(300);
                    }
                }
            } catch (e) {
                console.error('[GRI] Toggle fields error:', e);
            }
        },

        /**
         * Update coupon amount label based on type
         */
        updateCouponAmountLabel: function() {
            try {
                const couponType = getFieldValue($(this));
                const $amountField = $('#gri_coupon_amount');

                if (!$amountField.length) {
                    return;
                }

                const $label = $amountField.closest('tr').find('th label');

                let labelText = 'Coupon Amount';
                let placeholder = '';

                switch(couponType) {
                    case 'percent':
                        labelText = 'Discount Percentage (%)';
                        placeholder = 'e.g., 10 for 10% off';
                        $amountField.attr('max', '100');
                        break;
                    case 'fixed_cart':
                        labelText = 'Fixed Cart Discount';
                        placeholder = 'e.g., 5 for $5 off cart';
                        $amountField.removeAttr('max');
                        break;
                    case 'fixed_product':
                        labelText = 'Fixed Product Discount';
                        placeholder = 'e.g., 2 for $2 off per product';
                        $amountField.removeAttr('max');
                        break;
                }

                if ($label.length) {
                    $label.text(labelText);
                }
                $amountField.attr('placeholder', placeholder);
            } catch (e) {
                console.error('[GRI] Update label error:', e);
            }
        },

        /**
         * Initialize tooltips
         */
        initTooltips: function() {
            try {
                const tooltips = {
                    'gri_google_place_id': 'Find your Place ID by searching your business on Google Maps, clicking Share > Embed a map, and copying the ID from the code.',
                    'gri_email_delay': 'Recommended: 30-120 minutes. This gives customers time to write their review before receiving the coupon.',
                    'gri_coupon_validity': 'How long the coupon remains valid. Recommended: 30-60 days to create urgency.',
                    'gri_email_content': 'Use {coupon_code} as a placeholder for the actual coupon code.'
                };

                $.each(tooltips, function(fieldId, tooltipText) {
                    const $field = $('#' + fieldId);
                    if ($field.length && !$field.next('.gri-tooltip').length) {
                        const $tooltip = $('<span class="gri-tooltip">' +
                            '<span class="gri-help-tip"></span>' +
                            '<span class="gri-tooltip-text">' + tooltipText + '</span>' +
                            '</span>');
                        $field.after($tooltip);
                    }
                });
            } catch (e) {
                console.error('[GRI] Tooltip init error:', e);
            }
        },

        /**
         * Initialize conditional field display
         */
        initConditionalFields: function() {
            try {
                const $enableCoupon = $('#gri_enable_coupon');
                if ($enableCoupon.length) {
                    this.toggleCouponFields.call($enableCoupon[0]);
                }
            } catch (e) {
                console.error('[GRI] Conditional fields error:', e);
            }
        },

        /**
         * Initialize copy buttons for code snippets
         */
        initCopyButtons: function() {
            try {
                $('.gri-code-snippet').each(function() {
                    const $snippet = $(this);
                    if (!$snippet.next('.button').length) {
                        const code = $snippet.text();

                        const $copyBtn = $('<button type="button" class="button button-small" style="margin-left: 10px;">Copy</button>');
                        $copyBtn.on('click', function(e) {
                            e.preventDefault();
                            GRI_Admin.copyToClipboard(code);
                            $(this).text('Copied!').prop('disabled', true);
                            setTimeout(function() {
                                $copyBtn.text('Copy').prop('disabled', false);
                            }, 2000);
                        });

                        $snippet.after($copyBtn);
                    }
                });
            } catch (e) {
                console.error('[GRI] Copy buttons error:', e);
            }
        },

        /**
         * Copy text to clipboard
         */
        copyToClipboard: function(text) {
            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(function() {
                        GRI_Admin.showNotice('Copied to clipboard', 'success');
                    }).catch(function() {
                        GRI_Admin.copyToClipboardFallback(text);
                    });
                } else {
                    GRI_Admin.copyToClipboardFallback(text);
                }
            } catch (e) {
                GRI_Admin.copyToClipboardFallback(text);
            }
        },

        /**
         * Fallback clipboard copy method
         */
        copyToClipboardFallback: function(text) {
            try {
                const $temp = $('<textarea>');
                $('body').append($temp);
                setFieldValue($temp, text);
                $temp[0].select();
                document.execCommand('copy');
                $temp.remove();
                this.showNotice('Copied to clipboard', 'success');
            } catch (e) {
                console.error('[GRI] Clipboard error:', e);
            }
        },

        /**
         * Initialize email preview
         */
        initPreview: function() {
            try {
                const $emailContent = $('#gri_email_content');

                if (!$emailContent.length || $('#gri-email-preview').length) {
                    return;
                }

                const $preview = $('<div class="gri-info-card" style="margin-top: 20px;">' +
                    '<h3>Email Preview</h3>' +
                    '<div id="gri-email-preview" style="padding: 15px; background: #f6f7f7; border-radius: 4px;"></div>' +
                    '</div>');

                $emailContent.closest('td').append($preview);
                this.updateEmailPreview();
            } catch (e) {
                console.error('[GRI] Preview init error:', e);
            }
        },

        /**
         * Update email preview
         */
        updateEmailPreview: function() {
            try {
                const $emailContent = $('#gri_email_content');
                const $preview = $('#gri-email-preview');

                if (!$emailContent.length || !$preview.length) {
                    return;
                }

                const content = getFieldValue($emailContent);
                const previewContent = content.replace(/{coupon_code}/g, '<strong style="color: #2271b1;">REVIEW-SAMPLE123</strong>');
                $preview.html(previewContent || '<em>Preview will appear here...</em>');
            } catch (e) {
                console.error('[GRI] Preview update error:', e);
            }
        },

        /**
         * Handle form submission
         */
        handleFormSubmit: function(e) {
            try {
                let isValid = true;
                const self = this;

                $('input[type="text"], input[type="number"], textarea').each(function() {
                    try {
                        if ($(this).length && !self.validateField.call(this)) {
                            isValid = false;
                        }
                    } catch (err) {
                        console.error('[GRI] Validation error:', err);
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    this.showNotice('Please fix the errors before saving', 'error');

                    const $firstError = $('.error').first();
                    if ($firstError.length) {
                        $('html, body').animate({
                            scrollTop: $firstError.offset().top - 100
                        }, 500);
                    }

                    return false;
                }

                const $submitBtn = $('.submit .button-primary');
                if ($submitBtn.length) {
                    $submitBtn.addClass('loading').prop('disabled', true);
                }
                this.formIsDirty = false;
            } catch (e) {
                console.error('[GRI] Form submit error:', e);
            }
        },

        markFormAsDirty: function() {
            GRI_Admin.formIsDirty = true;
        },

        warnUnsavedChanges: function(e) {
            if (this.formIsDirty) {
                const message = 'You have unsaved changes. Are you sure you want to leave?';
                e.returnValue = message;
                return message;
            }
        },

        showNotice: function(message, type) {
            try {
                type = type || 'info';

                $('.notice.is-dismissible').remove();

                const $notice = $('<div class="notice notice-' + type + ' is-dismissible">' +
                    '<p>' + message + '</p>' +
                    '<button type="button" class="notice-dismiss">' +
                    '<span class="screen-reader-text">Dismiss this notice.</span>' +
                    '</button>' +
                    '</div>');

                const $target = $('.wrap h1');
                if ($target.length) {
                    $target.after($notice);

                    $notice.find('.notice-dismiss').on('click', function() {
                        $notice.fadeOut(300, function() {
                            $(this).remove();
                        });
                    });

                    setTimeout(function() {
                        $notice.fadeOut(300, function() {
                            $(this).remove();
                        });
                    }, 5000);
                }
            } catch (e) {
                console.error('[GRI] Show notice error:', e);
            }
        },

        formIsDirty: false
    };

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        try {
            if ($('body').hasClass('woocommerce_page_google-review-incentive') || 
                $('#gri_google_place_id').length) {
                GRI_Admin.init();
            }
        } catch (e) {
            console.error('[GRI] Failed to initialize:', e);
        }
    });

    window.GRI_Admin = GRI_Admin;

})(jQuery);
