/**
 * Restaurant SEO Generator Admin JavaScript
 */

(function($) {
    'use strict';

    // Initialize on document ready
    $(document).ready(function() {
        initDeleteSite();
    });

    /**
     * Delete site handler
     */
    function initDeleteSite() {
        $(document).on('click', '.rsg-delete-site', function(e) {
            e.preventDefault();

            if (!confirm('Are you sure you want to delete this site? This will delete all generated pages as well. This cannot be undone.')) {
                return;
            }

            const siteId = $(this).data('site-id');
            const $card = $(this).closest('.rsg-site-card');

            $.ajax({
                url: rsgAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'rsg_delete_site',
                    nonce: rsgAdmin.nonce,
                    site_id: siteId
                },
                success: function(response) {
                    if (response.success) {
                        $card.fadeOut(300, function() {
                            $(this).remove();

                            // Check if there are any sites left
                            if ($('.rsg-site-card').length === 0) {
                                location.reload();
                            }
                        });
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('Error deleting site. Please try again.');
                }
            });
        });
    }

    /**
     * Show loading overlay
     */
    function showLoading(message) {
        if ($('#rsg-loading-overlay').length === 0) {
            $('body').append(
                '<div id="rsg-loading-overlay" style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.7);z-index:999999;display:flex;align-items:center;justify-content:center;">' +
                '<div style="background:#fff;padding:30px;border-radius:8px;text-align:center;">' +
                '<div class="rsg-spinner"></div>' +
                '<p style="margin-top:15px;font-size:16px;">' + message + '</p>' +
                '</div>' +
                '</div>'
            );
        } else {
            $('#rsg-loading-overlay').show();
        }
    }

    /**
     * Hide loading overlay
     */
    function hideLoading() {
        $('#rsg-loading-overlay').fadeOut(300);
    }

    /**
     * Show notification
     */
    function showNotification(message, type) {
        type = type || 'success';

        const $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
        $('.rsg-wrap h1, .wrap h1').first().after($notice);

        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $notice.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);

        // Make dismissible work
        $notice.on('click', '.notice-dismiss', function() {
            $notice.fadeOut(300, function() {
                $(this).remove();
            });
        });

        // Scroll to top
        $('html, body').animate({
            scrollTop: 0
        }, 300);
    }

    /**
     * Copy text to clipboard
     */
    function copyToClipboard(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(text);
        } else {
            // Fallback for older browsers
            const $temp = $('<textarea>');
            $('body').append($temp);
            $temp.val(text).select();
            document.execCommand('copy');
            $temp.remove();
            return Promise.resolve();
        }
    }

    /**
     * Format number with commas
     */
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    /**
     * Validate URL
     */
    function isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }

    /**
     * Sanitize HTML
     */
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    /**
     * Get intent badge color class
     */
    function getIntentColorClass(intent) {
        const intentMap = {
            'transactional': 'intent-transactional',
            'commercial': 'intent-commercial',
            'informational': 'intent-informational',
            'navigational': 'intent-navigational'
        };
        return intentMap[intent] || 'intent-informational';
    }

    /**
     * Debounce function
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Export utility functions to global scope
    window.rsgUtils = {
        showLoading: showLoading,
        hideLoading: hideLoading,
        showNotification: showNotification,
        copyToClipboard: copyToClipboard,
        formatNumber: formatNumber,
        isValidUrl: isValidUrl,
        escapeHtml: escapeHtml,
        getIntentColorClass: getIntentColorClass,
        debounce: debounce
    };

})(jQuery);
