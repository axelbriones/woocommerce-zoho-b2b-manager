/**
 * Frontend-specific JavaScript for WooCommerce Zoho B2B Manager.
 *
 * @link       https://example.com/woocommerce-zoho-b2b-manager
 * @since      1.0.0
 */

(function( $ ) {
	'use strict';

	$(document).ready(function() {

        /**
         * Wishlist AJAX Handler
         * Uses event delegation for dynamically loaded products (e.g., quick view, infinite scroll)
         */
        $(document).on('click', '.wczb2b-wishlist-button', function(e) {
            e.preventDefault();
            var $button = $(this);
            var $wrapper = $button.closest('.wczb2b-wishlist-button-wrapper'); // Target the wrapper
            var product_id = $button.data('product-id');
            var variation_id = $button.data('variation-id') || 0;
            var ajax_action_name = $button.data('action'); // This should be 'wczb2b_add_to_wishlist' or 'wczb2b_remove_from_wishlist'

            if ($button.hasClass('loading') || !ajax_action_name) {
                return; // Prevent multiple clicks or if action is not defined
            }

            $button.addClass('loading');
            $button.prop('disabled', true);
            // You can add a visual spinner to $wrapper here if desired
            // $wrapper.append('<span class="wczb2b-wishlist-spinner spinner is-active"></span>');


            $.ajax({
                type: 'POST',
                url: wczb2b_wishlist_params.ajax_url,
                data: {
                    action: ajax_action_name,
                    nonce: wczb2b_wishlist_params.nonce,
                    product_id: product_id,
                    variation_id: variation_id
                },
                success: function(response) {
                    if (response.success) {
                        // Update button HTML directly from server response for consistency
                        if (response.data.button_html) {
                             $wrapper.html(response.data.button_html); // Replace content of the wrapper
                        } else {
                            // Fallback if button_html is not provided (less ideal)
                            var new_action_type, new_text;
                            if (ajax_action_name === 'wczb2b_add_to_wishlist') {
                                new_text = wczb2b_wishlist_params.i18n_added_to_wishlist;
                                new_action_type = 'wczb2b_remove_from_wishlist';
                                $button.addClass('added');
                            } else {
                                new_text = wczb2b_wishlist_params.i18n_add_to_wishlist;
                                new_action_type = 'wczb2b_add_to_wishlist';
                                $button.removeClass('added');
                            }
                            $button.text(new_text).data('action', new_action_type);
                            $button.removeClass('loading').prop('disabled', false); // Re-enable original button if not replaced
                        }

                        // Optionally update a wishlist counter somewhere on the page
                        if (typeof response.data.count !== 'undefined') {
                            $('.wczb2b-wishlist-counter').text(response.data.count); // Example counter class
                        }
                        // Consider a more user-friendly notification than alert:
                        // $(document.body).trigger('wc_add_to_cart_message', [response.data.message, true]); // Example using WC notice system

                        // Trigger custom event for other scripts to listen to
                        $(document.body).trigger('wczb2b_wishlist_updated', [response.data, product_id, variation_id]);

                    } else {
                        alert(response.data.message || wczb2b_wishlist_params.i18n_error_occurred);
                        // Re-enable button on error only if HTML is not replaced
                        if (!response.data.button_html) {
                            $button.removeClass('loading').prop('disabled', false);
                        } else {
                             // If HTML is replaced, the new button will be enabled by default.
                             // We might need to re-target the new button if it was also set to loading.
                             $wrapper.find('.wczb2b-wishlist-button').removeClass('loading').prop('disabled', false);
                        }
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Wishlist AJAX error:', textStatus, errorThrown);
                    alert(wczb2b_wishlist_params.i18n_error_occurred);
                    // Re-enable button on error
                    // $button.removeClass('loading').prop('disabled', false);
                    // This needs to be careful if $wrapper.html() was called.
                    $wrapper.find('.wczb2b-wishlist-button').removeClass('loading').prop('disabled', false);

                },
                complete: function() {
                    // Remove spinner if it was added to the wrapper
                    // $wrapper.find('.wczb2b-wishlist-spinner').remove();
                    // The button inside $wrapper might be new, so direct $button.removeClass might not work if HTML was replaced.
                    // The new button created by get_wishlist_button_html should not have 'loading' class by default.
                }
            });
        });

        // console.log('WooCommerce Zoho B2B Manager Frontend JS Loaded with Wishlist handlers');
	});

})( jQuery );
