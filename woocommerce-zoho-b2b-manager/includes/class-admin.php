<?php
/**
 * Admin area functionality for the plugin.
 * Handles the creation of settings pages and admin notices.
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

class WC_Zoho_B2B_Admin {

    private static $instance;
    public $applications_list_table_instance; // To hold the instance of the list table

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_plugin_settings' ) );
        add_action( 'admin_notices', array( $this, 'display_admin_notices' ) );
    }

    /**
     * Add options page under WooCommerce menu.
     */
    public function add_plugin_admin_menu() {
        // Main Menu Page
        add_menu_page(
            __( 'WooCommerce Zoho B2B Manager', 'wc-zoho-b2b' ),
            __( 'Zoho B2B', 'wc-zoho-b2b' ),
            'manage_woocommerce',
            'wczb2b-settings',
            array( $this, 'display_general_settings_page' ),
            'dashicons-briefcase',
            57
        );

        // General Settings Submenu (effectively the main page)
        add_submenu_page(
            'wczb2b-settings',
            __( 'B2B General Settings', 'wc-zoho-b2b' ),
            __( 'General Settings', 'wc-zoho-b2b' ),
            'manage_woocommerce',
            'wczb2b-settings',
            array( $this, 'display_general_settings_page' )
        );

        // Applications Submenu
        $applications_page_hook = add_submenu_page(
            'wczb2b-settings',
            __( 'B2B Applications', 'wc-zoho-b2b' ),
            __( 'Applications', 'wc-zoho-b2b' ),
            'manage_woocommerce',
            'wczb2b-applications',
            array( $this, 'display_applications_page' )
        );

        add_submenu_page(
            'wczb2b-settings',
            __( 'B2B User Roles & Pricing', 'wc-zoho-b2b' ),
            __( 'User Roles & Pricing', 'wc-zoho-b2b' ),
            'manage_woocommerce',
            'wczb2b-roles-pricing-settings',
            array( $this, 'display_roles_pricing_settings_page' )
        );

        // Zoho Integration Submenu
        add_submenu_page(
            'wczb2b-settings',
            __( 'B2B Zoho Integration Settings', 'wc-zoho-b2b' ),
            __( 'Zoho Integration', 'wc-zoho-b2b' ),
            'manage_woocommerce',
            'wczb2b-zoho-settings',
            array( $this, 'display_zoho_settings_page' )
        );

        // Hook to load WP_List_Table class and prepare items for the applications page.
        // The hook is 'load-{page_hook}' where {page_hook} is the value returned by add_submenu_page().
        if ($applications_page_hook) {
            add_action( "load-{$applications_page_hook}", array( $this, 'prepare_applications_list_table' ) );
        }
    }

    /**
     * Prepares the applications list table.
     * Loads the WP_List_Table class and instantiates it.
     * This is called on the 'load-{page_hook}' action for the applications page.
     */
    public function prepare_applications_list_table() {
        require_once WCZB2B_PLUGIN_DIR . 'admin/class-wczb2b-applications-list-table.php';
        $this->applications_list_table_instance = new WCZB2B_Applications_List_Table();
        // Process actions (approve/reject) before items are prepared, if actions are handled via GET requests to this page.
        // The actual action handling logic is in WC_Zoho_B2B_User_Manager, triggered by admin_action hooks.
        // We might need to check for success/error messages passed via query args here.

        // Note: process_bulk_action() is called within $this->applications_list_table_instance->prepare_items()
        // if the form submits to the same page.
    }


    /**
     * Register plugin settings using the Settings API.
     */
    public function register_plugin_settings() {
        // --- Group: General Settings ---
        $general_option_group = 'wczb2b_general_settings_group';
        $general_page_slug    = 'wczb2b-settings'; // Page slug where these settings are displayed

        register_setting($general_option_group, 'wc_zoho_b2b_registration_mode', array('sanitize_callback' => 'sanitize_text_field', 'default' => 'manual_approval', 'type' => 'string'));
        register_setting($general_option_group, 'wc_zoho_b2b_show_prices_logged_out', array('sanitize_callback' => 'sanitize_text_field', 'default' => 'no', 'type' => 'string'));
        register_setting($general_option_group, 'wc_zoho_b2b_default_user_role', array('sanitize_callback' => 'sanitize_text_field', 'default' => 'wczb2b_approved_customer', 'type' => 'string'));
        register_setting($general_option_group, 'wc_zoho_b2b_enable_wishlist', array('sanitize_callback' => 'sanitize_text_field', 'default' => 'yes', 'type' => 'string'));

        add_settings_section(
            'wczb2b_general_main_section',
            __('Main B2B Configuration', 'wc-zoho-b2b'),
            null,
            $general_page_slug
        );
        add_settings_field('wc_zoho_b2b_registration_mode', __('B2B Registration Mode', 'wc-zoho-b2b'), array($this, 'render_select_field'), $general_page_slug, 'wczb2b_general_main_section', array('option_name' => 'wc_zoho_b2b_registration_mode', 'options' => array('manual_approval' => __('Manual Approval by Admin', 'wc-zoho-b2b'), 'automatic' => __('Automatic Approval', 'wc-zoho-b2b')), 'description' => __('How new B2B applications are processed.', 'wc-zoho-b2b')));
        add_settings_field('wc_zoho_b2b_show_prices_logged_out', __('Show Prices to Guests', 'wc-zoho-b2b'), array($this, 'render_radio_field'), $general_page_slug, 'wczb2b_general_main_section', array('option_name' => 'wc_zoho_b2b_show_prices_logged_out', 'options' => array('yes' => __('Yes', 'wc-zoho-b2b'), 'no' => __('No', 'wc-zoho-b2b')), 'default' => 'no', 'description' => __('Allow non-logged-in users to see product prices.', 'wc-zoho-b2b')));
        add_settings_field('wc_zoho_b2b_default_user_role', __('Approved B2B User Role', 'wc-zoho-b2b'), array($this, 'render_select_role_field'), $general_page_slug, 'wczb2b_general_main_section', array('option_name' => 'wc_zoho_b2b_default_user_role', 'default' => 'wczb2b_approved_customer', 'description' => __('Role assigned to users upon B2B application approval.', 'wc-zoho-b2b')));
        add_settings_field('wc_zoho_b2b_enable_wishlist', __('Enable B2B Wishlist', 'wc-zoho-b2b'), array($this, 'render_radio_field'), $general_page_slug, 'wczb2b_general_main_section', array('option_name' => 'wc_zoho_b2b_enable_wishlist', 'options' => array('yes' => __('Yes', 'wc-zoho-b2b'), 'no' => __('No', 'wc-zoho-b2b')), 'default' => 'yes', 'description' => __('Enable the wishlist functionality for B2B users.', 'wc-zoho-b2b')));

        // --- Group: User Roles & Pricing Settings ---
        $pricing_option_group = 'wczb2b_roles_pricing_settings_group';
        $pricing_page_slug    = 'wczb2b-roles-pricing-settings';

        register_setting($pricing_option_group, 'wc_zoho_b2b_pricing_method', array('sanitize_callback' => 'sanitize_text_field', 'default' => 'percentage_discount', 'type' => 'string'));
        register_setting($pricing_option_group, 'wc_zoho_b2b_role_discounts', array('sanitize_callback' => array($this, 'sanitize_role_discounts'), 'default' => array(), 'type' => 'array'));

        add_settings_section('wczb2b_pricing_method_section', __('B2B Pricing Method', 'wc-zoho-b2b'), null, $pricing_page_slug);
        add_settings_field('wc_zoho_b2b_pricing_method', __('Pricing Method', 'wc-zoho-b2b'), array($this, 'render_select_field'), $pricing_page_slug, 'wczb2b_pricing_method_section', array('option_name' => 'wc_zoho_b2b_pricing_method', 'options' => array('percentage_discount' => __('Percentage Discount by Role', 'wc-zoho-b2b'), 'fixed_price' => __('Fixed Price per Product (via Product Edit)', 'wc-zoho-b2b')), 'description' => __('Select the primary B2B pricing strategy.', 'wc-zoho-b2b')));

        add_settings_section('wczb2b_role_discounts_section', __('Role-Based Discounts (%)', 'wc-zoho-b2b'), array($this, 'render_role_discounts_section_description'), $pricing_page_slug);
        $editable_roles = get_editable_roles();
        foreach ($editable_roles as $role_key => $role_details) {
            if (strpos($role_key, 'wczb2b_') === 0 || $role_key === 'customer' || apply_filters('wczb2b_show_discount_for_role', false, $role_key)) {
                add_settings_field('wc_zoho_b2b_role_discount_' . $role_key, $role_details['name'], array($this, 'render_role_discount_field'), $pricing_page_slug, 'wczb2b_role_discounts_section', array('role_key' => $role_key, 'option_name_base' => 'wc_zoho_b2b_role_discounts'));
            }
        }

        // --- Group: Zoho Integration Settings ---
        // Options are registered here, but fields are mostly rendered in the partial due to complexity (button, dynamic text)
        $zoho_option_group = 'wczb2b_zoho_settings_group';
        register_setting( $zoho_option_group, WC_Zoho_B2B_Zoho_Integration::OPT_B2B_ZOHO_ENABLED, array('sanitize_callback' => 'sanitize_text_field', 'type' => 'string', 'default' => 'no'));
        register_setting( $zoho_option_group, WC_Zoho_B2B_Zoho_Integration::OPT_B2B_USE_MAIN_PLUGIN_CONFIG, array('sanitize_callback' => 'sanitize_text_field', 'type' => 'string', 'default' => 'yes'));
        register_setting( $zoho_option_group, WC_Zoho_B2B_Zoho_Integration::OPT_B2B_CLIENT_ID, array('sanitize_callback' => 'sanitize_text_field', 'type' => 'string'));
        register_setting( $zoho_option_group, WC_Zoho_B2B_Zoho_Integration::OPT_B2B_CLIENT_SECRET, array('sanitize_callback' => 'sanitize_text_field', 'type' => 'string')); // For sensitive data, consider custom sanitization or store encrypted.
        register_setting( $zoho_option_group, WC_Zoho_B2B_Zoho_Integration::OPT_B2B_DOMAIN, array('sanitize_callback' => 'sanitize_text_field', 'type' => 'string', 'default' => 'com'));
        register_setting( $zoho_option_group, 'wc_zoho_b2b_zoho_sync_users', array('sanitize_callback' => 'sanitize_text_field', 'type' => 'string', 'default' => 'no'));
        register_setting( $zoho_option_group, 'wc_zoho_b2b_zoho_sync_pricing', array('sanitize_callback' => 'sanitize_text_field', 'type' => 'string', 'default' => 'no'));
        // Access tokens (OPT_B2B_ACCESS_TOKEN, etc.) are saved programmatically, not directly via form submission of these fields.
    }

    /** Sanitize role discounts array */
    public function sanitize_role_discounts($input) {
        $sanitized_input = array();
        if (is_array($input)) {
            foreach ($input as $role => $discount) {
                $sanitized_input[sanitize_key($role)] = ('' === $discount) ? '' : floatval($discount); // Allow empty string to remove discount
            }
        }
        return $sanitized_input;
    }

    // --- Render Callbacks for Settings API Fields ---
    public function render_text_field($args) {
        $option_name = $args['option_name'];
        $value = get_option($option_name, isset($args['default']) ? $args['default'] : '');
        $class = isset($args['class']) ? $args['class'] : 'regular-text';
        echo '<input type="text" id="' . esc_attr($option_name) . '" name="' . esc_attr($option_name) . '" value="' . esc_attr($value) . '" class="' . esc_attr($class) . '" />';
        if (isset($args['description'])) {
            echo '<p class="description">' . wp_kses_post($args['description']) . '</p>';
        }
    }

    public function render_radio_field($args) {
        $option_name = $args['option_name'];
        $options = $args['options'];
        $default = isset($args['default']) ? $args['default'] : (is_array($options) && !empty($options) ? key($options) : '');
        $current_value = get_option($option_name, $default);

        echo '<fieldset>';
        foreach ($options as $value => $label) {
            echo '<label style="margin-right: 15px;">';
            echo '<input type="radio" name="' . esc_attr($option_name) . '" value="' . esc_attr($value) . '" ' . checked($current_value, $value, false) . ' /> ';
            echo esc_html($label);
            echo '</label>';
        }
        echo '</fieldset>';
        if (isset($args['description'])) {
            echo '<p class="description">' . wp_kses_post($args['description']) . '</p>';
        }
    }

    public function render_select_field($args) {
        $option_name = $args['option_name'];
        $options = $args['options'];
        $default = isset($args['default']) ? $args['default'] : (is_array($options) && !empty($options) ? key($options) : '');
        $current_value = get_option($option_name, $default);

        echo '<select id="' . esc_attr($option_name) . '" name="' . esc_attr($option_name) . '">';
        foreach ($options as $value => $label) {
            echo '<option value="' . esc_attr($value) . '" ' . selected($current_value, $value, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        if (isset($args['description'])) {
            echo '<p class="description">' . wp_kses_post($args['description']) . '</p>';
        }
    }

    public function render_select_role_field($args) {
        $option_name = $args['option_name'];
        $default = isset($args['default']) ? $args['default'] : 'wczb2b_approved_customer';
        $current_value = get_option($option_name, $default);
        $editable_roles = get_editable_roles();

        echo '<select id="' . esc_attr($option_name) . '" name="' . esc_attr($option_name) . '">';
        foreach ($editable_roles as $role_key => $role_details) {
            echo '<option value="' . esc_attr($role_key) . '" ' . selected($current_value, $role_key, false) . '>' . esc_html($role_details['name']) . '</option>';
        }
        echo '</select>';
        if (isset($args['description'])) {
            echo '<p class="description">' . wp_kses_post($args['description']) . '</p>';
        }
    }

    public function render_role_discounts_section_description() {
        echo '<p>' . esc_html__('Define percentage discounts for specific user roles. Enter a number (e.g., 10 for 10%). Leave blank or 0 for no discount.', 'wc-zoho-b2b') . '</p>';
    }

    public function render_role_discount_field($args) {
        $option_name_base = $args['option_name_base']; // e.g., 'wc_zoho_b2b_role_discounts'
        $role_key = $args['role_key'];
        $option_values = get_option($option_name_base, array());
        $value = isset($option_values[$role_key]) ? $option_values[$role_key] : '';

        echo '<input type="number" step="0.01" min="0" max="100" id="' . esc_attr($option_name_base . '_' . $role_key) . '" name="' . esc_attr($option_name_base . '[' . $role_key . ']') . '" value="' . esc_attr($value) . '" class="small-text" placeholder="' . esc_attr__('e.g., 10', 'wc-zoho-b2b') . '" /> %';
    }

    // --- Display Callbacks for Admin Pages ---
    public function display_general_settings_page() {
        require_once WCZB2B_PLUGIN_DIR . 'admin/partials/admin-general-settings.php';
    }

    public function display_applications_page() {
        require_once WCZB2B_PLUGIN_DIR . 'admin/partials/admin-applications.php';
    }

    public function display_roles_pricing_settings_page() {
        require_once WCZB2B_PLUGIN_DIR . 'admin/partials/admin-pricing-settings.php';
    }

    public function display_zoho_settings_page() {
        if (isset($_GET['action']) && $_GET['action'] === 'wczb2b_oauth_callback' && isset($_GET['code'])) {
            $this->handle_zoho_oauth_callback();
        }
        require_once WCZB2B_PLUGIN_DIR . 'admin/partials/admin-zoho-settings.php';
    }

    private function handle_zoho_oauth_callback() {
        wczb2b_log('Zoho OAuth callback initiated in WC_Zoho_B2B_Admin.', 'info');

        $state = isset( $_GET['state'] ) ? sanitize_text_field( wp_unslash( $_GET['state'] ) ) : null;
        // It's good practice to use a state parameter for CSRF protection.
        // For this example, we'll assume a simple nonce check if state was used.
        // if ( ! $state || ! wp_verify_nonce( $state, 'wczb2b_zoho_oauth_authorize_state' ) ) {
        //    wczb2b_log('Zoho OAuth state verification failed.', 'error');
        //    wp_redirect(admin_url('admin.php?page=wczb2b-zoho-settings&oauth_status=state_error'));
        //    exit;
        // }

        $authorization_code = isset( $_GET['code'] ) ? sanitize_text_field( wp_unslash( $_GET['code'] ) ) : null;
        if ( ! $authorization_code ) {
            wczb2b_log('Zoho OAuth callback: Authorization code not found.', 'error');
            wp_redirect(admin_url('admin.php?page=wczb2b-zoho-settings&oauth_status=no_code'));
            exit;
        }

        $zoho_integration = WC_Zoho_B2B_Zoho_Integration::get_instance();
        $zoho_integration->load_settings(); // Ensure settings are fresh

        if ( ! $zoho_integration->is_client_fully_configured() ) {
             wczb2b_log('Zoho OAuth callback: Client ID, Secret, or Domain not configured when attempting token exchange.', 'error');
             wp_redirect(admin_url('admin.php?page=wczb2b-zoho-settings&oauth_status=config_error'));
             exit;
        }

        $token_url = "https://accounts.zoho." . $zoho_integration->get_zoho_domain() . "/oauth/v2/token";
        $response = wp_remote_post( $token_url, array(
            'method'    => 'POST',
            'timeout'   => 45,
            'body'      => array(
                'code'           => $authorization_code,
                'client_id'      => $zoho_integration->get_client_id(),
                'client_secret'  => $zoho_integration->get_client_secret(),
                'redirect_uri'   => $zoho_integration->get_redirect_uri(),
                'grant_type'     => 'authorization_code',
            ),
        ));

        $redirect_status = 'failed';
        if ( is_wp_error( $response ) ) {
            wczb2b_log('Zoho OAuth token exchange WP_Error: ' . $response->get_error_message(), 'error');
        } else {
            $response_code = wp_remote_retrieve_response_code( $response );
            $response_body = json_decode( wp_remote_retrieve_body( $response ), true );

            if ( $response_code === 200 && isset( $response_body['access_token'] ) ) {
                $zoho_integration->update_tokens(
                    $response_body['access_token'],
                    $response_body['expires_in'],
                    isset( $response_body['refresh_token'] ) ? $response_body['refresh_token'] : null
                );
                wczb2b_log('Zoho OAuth tokens obtained and saved successfully via Admin callback.', 'info');
                $redirect_status = 'success';
            } else {
                $error_message = isset($response_body['error']) ? $response_body['error'] : 'Unknown error during token exchange.';
                wczb2b_log('Zoho OAuth token exchange failed. RC: ' . $response_code . ' Body: ' . print_r($response_body, true), 'error');
            }
        }
        wp_redirect(admin_url('admin.php?page=wczb2b-zoho-settings&oauth_status=' . $redirect_status));
        exit;
    }

    /**
     * Display admin notices (like activation, OAuth status).
     */
    public function display_admin_notices() {
        // Activation Notice
        if ( get_transient( 'wczb2b_admin_notice_activation' ) ) {
            ?>
            <div class="notice notice-success is-dismissible wczb2b-admin-notice">
                <p><?php printf( esc_html__( 'WooCommerce Zoho B2B Manager activated! Please configure its settings under WooCommerce > Zoho B2B > %1$sGeneral Settings%2$s.', 'wc-zoho-b2b' ), '<a href="' . esc_url( admin_url( 'admin.php?page=wczb2b-settings' ) ) . '">', '</a>' ); ?></p>
            </div>
            <?php
            delete_transient( 'wczb2b_admin_notice_activation' );
        }

        // OAuth Status Notices (already handled by settings_errors() on the specific page, but could add more general ones here if needed)
        // Example: if (isset($_GET['page']) && $_GET['page'] === 'wczb2b-zoho-settings' && isset($_GET['oauth_status'])) { ... }
    }
}
?>
