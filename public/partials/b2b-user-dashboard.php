<?php
/**
 * Provides the view for the B2B user's main dashboard area within "My Account".
 * This could be an endpoint in WooCommerce My Account page.
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

// This template displays the main dashboard for logged-in B2B users.
// It could show quick stats, links to B2B features, company info, recent B2B orders, etc.
?>

<div class="wczb2b-user-dashboard-wrapper">
    <h2><?php esc_html_e( 'B2B Dashboard', 'wc-zoho-b2b' ); ?></h2>

    <p>
        <?php
        // $current_user = wp_get_current_user();
        // printf(
        //    esc_html__( 'Hello %1$s (not %1$s? %2$sLog out%3$s)', 'wc-zoho-b2b' ),
        // '<strong>' . esc_html( $current_user->display_name ) . '</strong>',
        // '<a href="' . esc_url( wc_logout_url( wc_get_page_permalink( 'myaccount' ) ) ) . '">',
        // '</a>'
        // );
        ?>
    </p>

    <p>
        <?php
        // esc_html_e( 'From your B2B dashboard you can view your recent B2B orders, manage your company profile, edit your password and account details, and access B2B specific tools like wishlists or quick order forms.', 'wc-zoho-b2b' );
        ?>
    </p>
    <p><em><?php esc_html_e( 'Welcome message and overview of B2B dashboard features.', 'wc-zoho-b2b' ); ?></em></p>

    <div class="wczb2b-dashboard-sections">
        <div class="wczb2b-dashboard-section">
            <h3><?php esc_html_e( 'Company Profile', 'wc-zoho-b2b' ); ?></h3>
            <p><em><?php esc_html_e( 'Link to edit company details (if applicable).', 'wc-zoho-b2b' ); ?></em></p>
            <?php
            // Display some company details if stored (e.g., company name, tax ID)
            // $company_name = get_user_meta( get_current_user_id(), 'billing_company', true );
            // if($company_name) {
            //    echo '<p>' . sprintf(esc_html__('Company: %s', 'wc-zoho-b2b'), esc_html($company_name)) . '</p>';
            // }
            ?>
            <p><a href="<?php /* echo esc_url( wc_customer_edit_account_url() ); */ ?>"><?php esc_html_e( 'Edit Account Details', 'wc-zoho-b2b' ); ?></a></p>
        </div>

        <div class="wczb2b-dashboard-section">
            <h3><?php esc_html_e( 'Quick Actions', 'wc-zoho-b2b' ); ?></h3>
            <ul>
                <li><a href="<?php /* echo esc_url( wc_get_endpoint_url( 'orders', '', wc_get_page_permalink( 'myaccount' ) ) ); */ ?>"><?php esc_html_e( 'View Recent Orders', 'wc-zoho-b2b' ); ?></a></li>
                <li><a href="<?php /* echo esc_url( wc_get_endpoint_url( 'wishlist', '', wc_get_page_permalink( 'myaccount' ) ) ); */ // Assuming 'wishlist' is a registered endpoint ?>"><?php esc_html_e( 'My Wishlist', 'wc-zoho-b2b' ); ?></a></li>
                <?php // Add link to quick order form if implemented ?>
            </ul>
            <p><em><?php esc_html_e( 'Links to Recent Orders, Wishlist, Quick Order Form, etc.', 'wc-zoho-b2b' ); ?></em></p>
        </div>
    </div>

    <?php // do_action( 'wczb2b_after_b2b_user_dashboard', get_current_user_id() ); ?>
</div>
