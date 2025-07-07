<?php
/**
 * Plugin Name:       WooCommerce Zoho B2B Manager
 * Plugin URI:        https://example.com/woocommerce-zoho-b2b-manager
 * Description:       Extends WooCommerce with B2B/B2C functionalities and integrates with Zoho, complementing the WooCommerce Zoho Integration plugin.
 * Version:           1.0.0
 * Author:            Your Name or Company
 * Author URI:        https://example.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wc-zoho-b2b
 * Domain Path:       /languages
 * WC requires at least: 9.0
 * WC tested up to: 9.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Check if WooCommerce is active.
 * We do this check early. If WooCommerce is not active, we display an admin notice and halt further execution of our plugin.
 */
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
    add_action( 'admin_notices', 'wczb2b_woocommerce_missing_notice' );
    return; // Stop further plugin execution if WooCommerce is not active.
}

/**
 * Admin notice for missing WooCommerce.
 * This function is only defined and hooked if the above check fails.
 */
function wczb2b_woocommerce_missing_notice() {
    ?>
    <div class="error">
        <p><?php esc_html_e( 'WooCommerce Zoho B2B Manager requires WooCommerce to be installed and active. Please install and activate WooCommerce.', 'wc-zoho-b2b' ); ?></p>
    </div>
    <?php
}

// Define plugin constants
define( 'WCZB2B_VERSION', '1.0.0' );
define( 'WCZB2B_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WCZB2B_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WCZB2B_PLUGIN_FILE', __FILE__ );
define( 'WCZB2B_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );


/**
 * The main plugin class.
 */
final class WooCommerce_Zoho_B2B_Manager {

    private static $instance;

    // Core class instances
    public $compatibility;
    public $admin;
    public $frontend;
    public $user_manager;
    public $pricing_manager;
    public $product_manager;
    public $order_manager;
    public $wishlist_manager;
    public $zoho_integration;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        // It's crucial to load dependencies before trying to use their classes in hooks.
        // So, call load_dependencies directly or ensure it runs before hooks are set up.
        $this->load_dependencies(); // Load dependencies right away.

        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        // init_components will be hooked to plugins_loaded to ensure all plugins (like WC) are loaded.
        add_action( 'plugins_loaded', array( $this, 'init_components' ), 15 );

        // Activation and deactivation hooks are registered here.
        // The actual logic will be delegated to the Installer class.
        register_activation_hook( WCZB2B_PLUGIN_FILE, array( 'WC_Zoho_B2B_Installer', 'activate' ) );
        register_deactivation_hook( WCZB2B_PLUGIN_FILE, array( 'WC_Zoho_B2B_Installer', 'deactivate' ) );
    }

    /**
     * Load required dependencies.
     *
     * @since 1.0.0
     */
    public function load_dependencies() {
        // Helper functions
        require_once WCZB2B_PLUGIN_DIR . 'includes/functions.php';

        // Installer class - needed for activation/deactivation hooks
        require_once WCZB2B_PLUGIN_DIR . 'includes/class-installer.php';

        // Compatibility class first, as other classes might depend on it
        require_once WCZB2B_PLUGIN_DIR . 'includes/class-compatibility.php';
        // $this->compatibility will be instantiated in init_components if still needed as a property

        // Core classes - these will be instantiated in init_components
        require_once WCZB2B_PLUGIN_DIR . 'includes/class-admin.php';
        require_once WCZB2B_PLUGIN_DIR . 'includes/class-frontend.php';
        require_once WCZB2B_PLUGIN_DIR . 'includes/class-user-manager.php';
        require_once WCZB2B_PLUGIN_DIR . 'includes/class-pricing-manager.php';
        require_once WCZB2B_PLUGIN_DIR . 'includes/class-product-manager.php';
        require_once WCZB2B_PLUGIN_DIR . 'includes/class-order-manager.php';
        require_once WCZB2B_PLUGIN_DIR . 'includes/class-wishlist-manager.php';
        require_once WCZB2B_PLUGIN_DIR . 'includes/class-zoho-integration.php';

        // Example: Admin specific files like list tables (if not handled by class-admin.php)
        // require_once WCZB2B_PLUGIN_DIR . 'admin/class-wczb2b-applications-list-table.php';
    }

    /**
     * Initialize plugin components.
     * Instantiates core classes and sets up hooks.
     * This runs on 'plugins_loaded' action.
     *
     * @since 1.0.0
     */
    public function init_components() {
        // Instantiate compatibility class here if it's to be accessed via $this->compatibility
        $this->compatibility     = WC_Zoho_B2B_Compatibility::get_instance();

        // Instantiate other core components
        $this->admin             = new WC_Zoho_B2B_Admin();
        $this->frontend          = new WC_Zoho_B2B_Frontend();
        $this->user_manager      = new WC_Zoho_B2B_User_Manager();
        $this->pricing_manager   = new WC_Zoho_B2B_Pricing_Manager();
        $this->product_manager   = new WC_Zoho_B2B_Product_Manager();
        $this->order_manager     = new WC_Zoho_B2B_Order_Manager();
        $this->wishlist_manager  = new WC_Zoho_B2B_Wishlist_Manager();
        $this->zoho_integration  = WC_Zoho_B2B_Zoho_Integration::get_instance(); // Use get_instance if it's a singleton

        // Enqueue scripts and styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    /**
     * Placeholder for activation logic.
     * Actual activation is handled by WC_Zoho_B2B_Installer::activate().
     * This method is kept to fulfill the registration of the hook in the constructor,
     * but the direct call is made to the static Installer method.
     *
     * @since 1.0.0
     */
    public function activate() {
        // Intentionally empty. Logic is in WC_Zoho_B2B_Installer::activate().
        // This is because register_activation_hook expects a callable,
        // and static calls are cleaner for this purpose.
    }

    /**
     * Placeholder for deactivation logic.
     * Actual deactivation is handled by WC_Zoho_B2B_Installer::deactivate().
     *
     * @since 1.0.0
     */
    public function deactivate() {
        // Intentionally empty. Logic is in WC_Zoho_B2B_Installer::deactivate().
    }

    /**
     * Load plugin textdomain for translation.
     *
     * @since 1.0.0
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'wc-zoho-b2b',
            false,
            dirname( WCZB2B_PLUGIN_BASENAME ) . '/languages/'
        );
    }

    /**
     * Enqueue frontend scripts and styles.
     *
     * @since 1.0.0
     */
    public function enqueue_frontend_assets() {
        wp_enqueue_style(
            'wczb2b-frontend-css',
            WCZB2B_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            WCZB2B_VERSION
        );
        wp_enqueue_script(
            'wczb2b-frontend-js',
            WCZB2B_PLUGIN_URL . 'assets/js/frontend.js',
            array( 'jquery' ),
            WCZB2B_VERSION,
            true // Load in footer
        );

        // Localize script with data for AJAX, including nonce for wishlist
        wp_localize_script(
            'wczb2b-frontend-js',
            'wczb2b_wishlist_params',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'wczb2b_wishlist_nonce' ), // Matches nonce check in PHP AJAX handlers
                'i18n_added_to_wishlist' => __( '✓ Added to Wishlist', 'wc-zoho-b2b' ),
                'i18n_add_to_wishlist'   => __( '♡ Add to Wishlist', 'wc-zoho-b2b' ),
                'i18n_remove_from_wishlist' => __( 'Remove from Wishlist', 'wc-zoho-b2b' ), // Not used yet in button text but good for tooltips
                'i18n_error_occurred'    => __( 'An error occurred. Please try again.', 'wc-zoho-b2b' ),
                // Add any other params needed by frontend JS
            )
        );
    }

    /**
     * Enqueue admin scripts and styles.
     *
     * @since 1.0.0
     * @param string $hook_suffix The current admin page.
     */
    public function enqueue_admin_assets( $hook_suffix ) {
        // Example: Load admin CSS/JS only on plugin's own settings pages
        // $plugin_pages = array( 'woocommerce_page_wc-zoho-b2b-settings', 'toplevel_page_wc-zoho-b2b-applications' ); // Example page slugs
        // if ( in_array( $hook_suffix, $plugin_pages ) ) {
            wp_enqueue_style(
                'wczb2b-admin-css',
                WCZB2B_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                WCZB2B_VERSION
            );
            wp_enqueue_script(
                'wczb2b-admin-js',
                WCZB2B_PLUGIN_URL . 'assets/js/admin.js',
                array( 'jquery' ),
                WCZB2B_VERSION,
                true // Load in footer
            );
        // }
    }
}

/**
 * Begins execution of the plugin.
 *
 * Instantiates the main plugin class after all plugins are loaded
 * to ensure all dependencies (like WooCommerce) are available.
 *
 * @since 1.0.0
 */
function wczb2b_run_plugin() {
    // The initial check for WooCommerce is done at the top of this file.
    // If WooCommerce wasn't active, the script would have exited.
    return WooCommerce_Zoho_B2B_Manager::get_instance();
}
// Initialize the plugin.
add_action( 'plugins_loaded', 'wczb2b_run_plugin', 10 ); // Priority 10 to allow other plugins to load.

?>
