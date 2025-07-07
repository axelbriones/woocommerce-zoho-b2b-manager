<?php
/**
 * B2B Checkout Template.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce-zoho-b2b-manager/checkout-b2b.php.
 * It might be used if B2B customers need a significantly different checkout process.
 *
 * @link       https://example.com/woocommerce-zoho-b2b-manager
 * @since      1.0.0
 *
 * @package    WooCommerce_Zoho_B2B_Manager
 * @subpackage WooCommerce_Zoho_B2B_Manager/templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// This template would be used if you need a custom checkout page for B2B users.
// More commonly, you might use hooks to modify the existing WooCommerce checkout.

// if ( ! wczb2b_is_b2b_customer() ) {
    // If not a B2B customer, fall back to standard checkout.
    // wc_get_template( 'checkout/form-checkout.php' );
    // return;
// }
?>
<div class="wczb2b-checkout-b2b-wrapper">
    <p><em><?php esc_html_e( 'This is a placeholder for the B2B Checkout Template.', 'wc-zoho-b2b' ); ?></em></p>
    <p><em><?php esc_html_e( 'If used, this template would provide a custom layout for the checkout page viewed by B2B customers.', 'wc-zoho-b2b' ); ?></em></p>
    <p><em><?php esc_html_e( 'It might include fields for Purchase Order Numbers, different billing/shipping field requirements, specific payment gateway displays, or terms and conditions.', 'wc-zoho-b2b' ); ?></em></p>

    <form name="checkout" method="post" class="checkout woocommerce-checkout wczb2b-checkout" action="<?php // echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

        <?php // if ( $checkout->get_checkout_fields() ) : ?>

            <?php // do_action( 'wczb2b_checkout_before_customer_details' ); ?>

            <div class="col2-set" id="customer_details">
                <div class="col-1">
                    <?php // do_action( 'wczb2b_checkout_billing' ); // Could output B2B specific billing fields ?>
                </div>

                <div class="col-2">
                    <?php // do_action( 'wczb2b_checkout_shipping' ); // Could output B2B specific shipping fields ?>
                </div>
            </div>

            <?php // do_action( 'wczb2b_checkout_after_customer_details' ); ?>

        <?php // endif; ?>

        <?php // do_action( 'wczb2b_checkout_before_order_review_heading' ); ?>

        <h3 id="order_review_heading"><?php esc_html_e( 'Your B2B order', 'wc-zoho-b2b' ); ?></h3>

        <?php // do_action( 'wczb2b_checkout_before_order_review' ); ?>

        <div id="order_review" class="woocommerce-checkout-review-order">
            <?php // do_action( 'wczb2b_checkout_order_review' ); // Review order table, payment methods ?>
            <p><em><?php esc_html_e( 'Custom B2B order review and payment section here.', 'wc-zoho-b2b' ); ?></em></p>
        </div>

        <?php // do_action( 'wczb2b_checkout_after_order_review' ); ?>

    </form>
    <?php // do_action( 'wczb2b_after_checkout_form', $checkout ); ?>
</div>
