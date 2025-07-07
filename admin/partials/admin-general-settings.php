<?php
/**
 * Provides the view for the general settings page of the plugin.
 *
 * @link       https://example.com/woocommerce-zoho-b2b-manager
 * @since      1.0.0
 *
 * @package    WooCommerce_Zoho_B2B_Manager
 * @subpackage WooCommerce_Zoho_B2B_Manager/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="wrap wczb2b-settings-wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <?php settings_errors(); // Display any errors/messages from Settings API ?>

    <form method="post" action="options.php">
        <?php
        // Output security fields for the registered setting group.
        settings_fields( 'wczb2b_general_settings_group' );

        // Output setting sections and fields for the 'wc-zoho-b2b-settings' page (or a specific section).
        // In WC_Zoho_B2B_Admin, fields are added to the 'wc-zoho-b2b-settings' page slug
        // and the 'wczb2b_general_main_section' section.
        do_settings_sections( 'wc-zoho-b2b-settings' );

        // You would add more sections and fields in WC_Zoho_B2B_Admin::register_plugin_settings()
        // For example:
        /*
        add_settings_field(
            'wc_zoho_b2b_show_prices_logged_out',
            __('Show Prices to Logged Out Users', 'wc-zoho-b2b'),
            array($this_admin_class_instance, 'render_show_prices_field'), // Callback in WC_Zoho_B2B_Admin
            'wc-zoho-b2b-settings',
            'wczb2b_general_main_section'
        );
        add_settings_field(
            'wc_zoho_b2b_default_user_role',
            __('Default Approved B2B Role', 'wc-zoho-b2b'),
            array($this_admin_class_instance, 'render_default_role_field'),
            'wc-zoho-b2b-settings',
            'wczb2b_general_main_section'
        );
         add_settings_field(
            'wc_zoho_b2b_enable_wishlist',
            __('Enable B2B Wishlist', 'wc-zoho-b2b'),
            array($this_admin_class_instance, 'render_enable_wishlist_field'),
            'wc-zoho-b2b-settings',
            'wczb2b_general_main_section'
        );
        */
        ?>

        <?php submit_button( __( 'Save General Settings', 'wc-zoho-b2b' ) ); ?>
    </form>
</div>
