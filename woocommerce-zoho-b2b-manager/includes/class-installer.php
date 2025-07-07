<?php
/**
 * Handles plugin activation, deactivation, and installation tasks like DB table creation.
 *
 * @link       https://example.com/woocommerce-zoho-b2b-manager
 * @since      1.0.0
 *
 * @package    WooCommerce_Zoho_B2B_Manager
 * @subpackage WooCommerce_Zoho_B2B_Manager/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WC_Zoho_B2B_Installer {

    /**
     * Activation hook.
     * Creates database tables, sets default options, registers roles.
     * This method is called from the main plugin file's activation hook.
     */
    public static function activate() {
        self::create_tables();
        self::register_user_roles();
        self::set_default_options();
        // flush_rewrite_rules(); // Uncomment if CPTs or custom endpoints are registered on activation that need rewrite rules.

        // Set a transient for an admin notice on first activation (optional)
        set_transient( 'wczb2b_admin_notice_activation', true, 5 * MINUTE_IN_SECONDS );

        wczb2b_log( 'WooCommerce Zoho B2B Manager activated successfully. Tables and roles checked/created. Version: ' . WCZB2B_VERSION, 'info' );
    }

    /**
     * Deactivation hook.
     * Cleans up if necessary (e.g., remove cron jobs).
     * This method is called from the main plugin file's deactivation hook.
     */
    public static function deactivate() {
        // Example: wp_clear_scheduled_hook( 'wczb2b_some_cron_hook' );
        // Consider if any options or roles should be cleaned up on deactivation,
        // but generally, it's better to do that on uninstall to preserve data.
        wczb2b_log( 'WooCommerce Zoho B2B Manager deactivated.', 'info' );
    }

    /**
     * Create custom database tables using dbDelta.
     */
    private static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php'; // Required for dbDelta()

        // --- Applications Table ---
        $table_name_applications = $wpdb->prefix . 'wc_zoho_b2b_applications';
        $sql_applications = "CREATE TABLE {$table_name_applications} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED DEFAULT NULL,
            company_name varchar(255) NOT NULL,
            tax_id varchar(50) DEFAULT NULL,
            business_type varchar(100) DEFAULT NULL,
            contact_person varchar(255) DEFAULT NULL,
            phone varchar(50) DEFAULT NULL,
            email varchar(100) NOT NULL,
            address text DEFAULT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending', /* pending, approved, rejected */
            zoho_contact_id varchar(50) DEFAULT NULL, /* Zoho Contact/Account ID */
            applied_date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            processed_date datetime DEFAULT NULL, /* Renamed from approved_date for clarity */
            processed_by bigint(20) UNSIGNED DEFAULT NULL, /* Renamed from approved_by */
            notes text DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY email (email),
            KEY status (status)
        ) {$charset_collate};";
        dbDelta( $sql_applications );
        wczb2b_log( "Checked/Created database table: {$table_name_applications}", 'debug' );

        // --- Wishlist Table ---
        $table_name_wishlist = $wpdb->prefix . 'wc_zoho_b2b_wishlist';
        $sql_wishlist = "CREATE TABLE {$table_name_wishlist} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            product_id bigint(20) UNSIGNED NOT NULL,
            variation_id bigint(20) UNSIGNED DEFAULT 0,
            date_added datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY user_product_variation (user_id, product_id, variation_id),
            KEY user_id (user_id),
            KEY product_id (product_id)
        ) {$charset_collate};";
        dbDelta( $sql_wishlist );
        wczb2b_log( "Checked/Created database table: {$table_name_wishlist}", 'debug' );
    }

    /**
     * Register custom B2B user roles.
     */
    private static function register_user_roles() {
        $customer_caps = get_role( 'customer' ) ? get_role( 'customer' )->capabilities : array( 'read' => true );

        add_role(
            'wczb2b_pending_customer',
            __( 'B2B Customer (Pending)', 'wc-zoho-b2b' ),
            $customer_caps
        );

        add_role(
            'wczb2b_approved_customer',
            __( 'B2B Customer', 'wc-zoho-b2b' ),
            // Start with customer capabilities, can be expanded via filters or settings
            $customer_caps
            // Example of adding a custom capability:
            // array_merge( $customer_caps, array( 'view_b2b_pricing' => true ) )
        );

        // Future: Allow admin to define more roles like 'b2b_wholesaler', 'b2b_distributor'
        // via the 'wc_zoho_b2b_custom_roles' option. These would also be registered here.
        wczb2b_log( "Checked/Created B2B user roles (wczb2b_pending_customer, wczb2b_approved_customer).", 'debug' );
    }

    /**
     * Set default options for the plugin on first activation.
     * These options are prefixed with 'wc_zoho_b2b_'.
     */
    private static function set_default_options() {
        $default_options = array(
            'registration_mode'         => 'manual_approval', // 'automatic', 'manual_approval', 'invitation_only'
            'show_prices_logged_out'    => 'no',        // 'yes', 'no'
            'default_user_role'         => 'wczb2b_approved_customer', // Role assigned on approval
            'enable_wishlist'           => 'yes',       // 'yes', 'no'
            'pricing_method'            => 'percentage_discount', // 'fixed_price', 'percentage_discount'
            // 'role_discounts' example: array('wczb2b_approved_customer' => 5) for 5%
            'role_discounts'            => array(),
            'zoho_enabled'              => 'no',        // Master switch for B2B Zoho features: 'yes', 'no'
            'use_main_plugin_config'    => 'yes',       // Try to use main Zoho plugin's config by default: 'yes', 'no'
            'zoho_sync_users'           => 'no',        // Sync users/applications to Zoho: 'yes', 'no'
            'zoho_sync_pricing'         => 'no',        // Sync B2B pricing with Zoho: 'yes', 'no'
            // 'tax_rate' - Decided to omit as default, too specific. Can be added if a clear need arises.
            'payment_methods_same'      => 'yes',       // 'yes' (B2B use same as B2C), 'no' (allow selecting specific for B2B)
            // 'minimum_quantities' - This will likely be per-product meta or complex global rules.
        );

        foreach ( $default_options as $option_key => $default_value ) {
            // Options are stored with 'wc_zoho_b2b_' prefix automatically by wczb2b_get_option,
            // so we add it here for consistency if checking/updating directly.
            $prefixed_option_key = 'wc_zoho_b2b_' . $option_key;
            if ( false === get_option( $prefixed_option_key ) ) {
                update_option( $prefixed_option_key, $default_value );
            }
        }
        wczb2b_log( "Default plugin options checked/set.", 'debug' );
    }
}
?>
