<?php
/**
 * Provides the view for the B2B User Roles & Pricing settings page/tab.
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

    <?php settings_errors(); ?>

    <form method="post" action="options.php">
        <?php
        settings_fields( 'wczb2b_roles_pricing_settings_group' );
        // Sections and fields for this page slug ('wczb2b-roles-pricing-settings')
        // will be defined in WC_Zoho_B2B_Admin::register_plugin_settings()

        // Example:
        /*
        // In WC_Zoho_B2B_Admin::register_plugin_settings():
        add_settings_section(
            'wczb2b_pricing_method_section',
            __('B2B Pricing Method', 'wc-zoho-b2b'),
            null,
            'wczb2b-roles-pricing-settings' // Page slug for this section
        );
        add_settings_field(
            'wc_zoho_b2b_pricing_method',
            __('Pricing Method', 'wc-zoho-b2b'),
            array($this_admin_class_instance, 'render_pricing_method_field'),
            'wczb2b-roles-pricing-settings', // Page slug
            'wczb2b_pricing_method_section'  // Section ID
        );

        add_settings_section(
            'wczb2b_role_discounts_section',
            __('Role-Based Discounts (%)', 'wc-zoho-b2b'),
            array($this_admin_class_instance, 'render_role_discounts_section_info'), // Callback for section description
            'wczb2b-roles-pricing-settings'
        );
        // Fields for each role would be added dynamically or explicitly here
        // For example, by iterating through editable_roles in the callback for the field or section
        $editable_roles = get_editable_roles();
        foreach ($editable_roles as $role_key => $role_details) {
            if (strpos($role_key, 'wczb2b_') === 0 || $role_key === 'customer' || user_can(wp_get_current_user(), 'administrator')) { // Filter relevant roles
                 add_settings_field(
                    'wc_zoho_b2b_role_discounts_' . $role_key,
                    $role_details['name'],
                    array($this_admin_class_instance, 'render_role_discount_field'),
                    'wczb2b-roles-pricing-settings',
                    'wczb2b_role_discounts_section',
                    array('role' => $role_key) // Pass role_key to callback
                );
            }
        }
        */
        ?>
        <p><em><?php esc_html_e( 'Placeholder: Settings fields for B2B User Roles & Pricing will be rendered here by `do_settings_sections(\'wczb2b-roles-pricing-settings\');`', 'wc-zoho-b2b' ); ?></em></p>
        <p><em><?php esc_html_e( 'This includes selecting pricing methods (fixed/percentage), defining discounts per role, and potentially managing B2B role capabilities.', 'wc-zoho-b2b' ); ?></em></p>
        <?php
        // This call will render all sections and fields added to the 'wczb2b-roles-pricing-settings' page.
        do_settings_sections( 'wczb2b-roles-pricing-settings' );
        ?>

        <?php submit_button( __( 'Save Pricing & Role Settings', 'wc-zoho-b2b' ) ); ?>
    </form>
</div>
