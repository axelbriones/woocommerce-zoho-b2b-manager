<?php
/**
 * B2B My Account Dashboard Template.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce-zoho-b2b-manager/my-account-b2b-dashboard.php.
 * This is intended to be the content for a custom My Account endpoint for B2B users.
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

// This template is for the content of a B2B specific dashboard page within My Account.
// It's distinct from public/partials/my-account-b2b.php which might be a more general shortcode
// or a modification to the main My Account page. This one is for a dedicated endpoint.

?>
<div class="wczb2b-my-account-dashboard-content">

    <h2><?php esc_html_e( 'B2B Account Dashboard', 'wc-zoho-b2b' ); ?></h2>

    <p>
        <?php
        /*
        printf(
            wp_kses(
                // translators: 1: user display name 2: logout url
                __( 'Hello %1$s (not %1$s? <a href="%2$s">Log out</a>)', 'wc-zoho-b2b' ),
                array(
                    'a' => array(
                        'href' => array(),
                    ),
                )
            ),
            '<strong>' . esc_html( $current_user->display_name ) . '</strong>',
            esc_url( wc_logout_url() )
        );
        */
        ?>
    </p>
    <p><em><?php esc_html_e( 'Welcome message for B2B user.', 'wc-zoho-b2b' ); ?></em></p>


    <p>
        <?php
        /*
        printf(
            wp_kses(
                // translators: %s: My Account URL
                __( 'From your account dashboard you can view your <a href="%1$s">recent orders</a>, manage your <a href="%2$s">shipping and billing addresses</a>, and <a href="%3$s">edit your password and account details</a>.', 'wc-zoho-b2b' ) . ' ',
                array(
                    'a' => array(
                        'href' => array(),
                    ),
                )
            ),
            esc_url( wc_get_endpoint_url( 'orders' ) ),
            esc_url( wc_get_endpoint_url( 'edit-address' ) ),
            esc_url( wc_get_endpoint_url( 'edit-account' ) )
        );
        */
        ?>
    </p>
    <p><em><?php esc_html_e( 'Standard My Account intro text, can be customized for B2B.', 'wc-zoho-b2b' ); ?></em></p>


    <?php // do_action( 'wczb2b_my_account_b2b_dashboard_content_start' ); ?>

    <h3><?php esc_html_e( 'B2B Quick Links', 'wc-zoho-b2b' ); ?></h3>
    <ul>
        <li><a href="<?php /* echo esc_url( wc_get_endpoint_url( 'orders' ) ); */ ?>"><?php esc_html_e( 'My B2B Orders', 'wc-zoho-b2b' ); ?></a></li>
        <li><a href="<?php /* echo esc_url( wc_get_endpoint_url( 'wishlist' ) ); */ // Assuming 'wishlist' is a custom endpoint ?>"><?php esc_html_e( 'My Wishlist', 'wc-zoho-b2b' ); ?></a></li>
        <li><a href="<?php /* echo esc_url( wc_get_endpoint_url( 'edit-account' ) ); */ ?>"><?php esc_html_e( 'Account Details', 'wc-zoho-b2b' ); ?></a></li>
        <li><a href="<?php /* echo esc_url( wc_get_endpoint_url( 'edit-address' ) ); */ ?>"><?php esc_html_e( 'Addresses', 'wc-zoho-b2b' ); ?></a></li>
        <?php // Add link to company profile page if separate ?>
        <?php // Add link to quick order form if available ?>
    </ul>
    <p><em><?php esc_html_e( 'List of B2B specific quick links.', 'wc-zoho-b2b' ); ?></em></p>


    <?php // Display summary of recent B2B orders, or pending application status etc. ?>
    <p><em><?php esc_html_e( 'Further B2B specific dashboard content (e.g., recent POs, account manager info) can go here.', 'wc-zoho-b2b' ); ?></em></p>


    <?php // do_action( 'wczb2b_my_account_b2b_dashboard_content_end' ); ?>
</div>
