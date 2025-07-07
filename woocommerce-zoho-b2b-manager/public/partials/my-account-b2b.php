<?php
/**
 * Provides the view for the B2B specific "My Account" dashboard or section.
 * This template can be loaded via a shortcode or by hooking into WooCommerce's My Account page.
 *
 * @link       https://example.com/woocommerce-zoho-b2b-manager
 * @since      1.0.0
 *
 * @package    WooCommerce_Zoho_B2B_Manager
 * @subpackage WooCommerce_Zoho_B2B_Manager/public/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// This file would display B2B specific information and links within the "My Account" area.
// e.g., B2B application status, quick order form link, wishlist link, company details.
?>

<div class="wczb2b-my-account-b2b-wrapper">
    <h2><?php esc_html_e( 'B2B Account Information', 'wc-zoho-b2b' ); ?></h2>

    <p><em><?php esc_html_e( 'B2B specific account details and quick links will go here.', 'wc-zoho-b2b' ); ?></em></p>

    <?php
    // Example: Display application status if user has applied
    // $user_id = get_current_user_id();
    // $application_status = get_user_meta( $user_id, 'wczb2b_application_status', true );
    // if ( $application_status ) {
    //     echo '<p>' . sprintf( esc_html__( 'Your B2B application status: %s', 'wc-zoho-b2b' ), '<strong>' . esc_html( ucfirst( $application_status ) ) . '</strong>' ) . '</p>';
    // }
    ?>

    <ul>
        <li><a href="<?php /* echo esc_url( wc_get_endpoint_url( 'b2b-dashboard', '', wc_get_page_permalink( 'myaccount' ) ) ); */ ?>"><?php esc_html_e( 'B2B Dashboard', 'wc-zoho-b2b' ); ?></a></li>
        <li><a href="<?php /* echo esc_url( wc_get_endpoint_url( 'wishlist', '', wc_get_page_permalink( 'myaccount' ) ) ); */ ?>"><?php esc_html_e( 'My Wishlist', 'wc-zoho-b2b' ); ?></a></li>
        <?php // Potentially link to company profile editing, past B2B orders with PO numbers, etc. ?>
    </ul>
     <p><em><?php esc_html_e( 'Links to B2B Dashboard, Wishlist, Company Profile, etc.', 'wc-zoho-b2b' ); ?></em></p>
</div>
