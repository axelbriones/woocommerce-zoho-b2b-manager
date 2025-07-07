<?php
/**
 * Handles compatibility with other plugins, especially WooCommerce Zoho Integration.
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

class WC_Zoho_B2B_Compatibility {

    // Define the path to the main plugin file of 'woocommerce-zoho-integration'.
    // This is based on the typical structure and the repository name.
    const MAIN_ZOHO_PLUGIN_PATH = 'woocommerce-zoho-integration/woocommerce-zoho-integration.php';

    // GUESS: The option name used by the main 'woocommerce-zoho-integration' plugin to store its settings.
    // This needs to be verified by inspecting the main plugin's code. Common patterns include 'plugin_slug_settings' or 'plugin_prefix_options'.
    // For example, if the main plugin uses 'wzc_settings' or 'zoho_integration_options'.
    // For the purpose of this example, let's assume a hypothetical option name.
    // UPDATE THIS CONSTANT if the actual option name is discovered.
    const MAIN_ZOHO_PLUGIN_GENERAL_SETTINGS_OPTION_NAME = 'wzc_general_settings'; // Example: For general settings like Client ID, Secret, Domain
    const MAIN_ZOHO_PLUGIN_TOKEN_OPTION_NAME = 'wzc_token_details'; // Example: For storing access/refresh tokens

    private static $instance;
    private $is_main_zoho_plugin_active = false;
    private $main_zoho_plugin_version = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor to prevent direct object creation.
     * Initializes the status of the main Zoho plugin.
     */
    private function __construct() {
        $this->check_main_zoho_plugin_status();
    }

    /**
     * Checks if the main 'WooCommerce Zoho Integration' plugin is active
     * and stores its version if available.
     */
    private function check_main_zoho_plugin_status() {
        if ( ! function_exists( 'is_plugin_active' ) ) {
            // Ensure is_plugin_active() is available, normally only in admin context.
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        if ( function_exists('is_plugin_active') && is_plugin_active( self::MAIN_ZOHO_PLUGIN_PATH ) ) {
            $this->is_main_zoho_plugin_active = true;
            $main_plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . self::MAIN_ZOHO_PLUGIN_PATH );
            if ( isset( $main_plugin_data['Version'] ) ) {
                $this->main_zoho_plugin_version = $main_plugin_data['Version'];
            }
        }
    }

    /**
     * Returns whether the main Zoho Integration plugin is active.
     *
     * @since 1.0.0
     * @return bool True if active, false otherwise.
     */
    public function is_main_zoho_plugin_active() {
        return $this->is_main_zoho_plugin_active;
    }

    /**
     * Returns the version of the main Zoho Integration plugin, if active.
     *
     * @since 1.0.0
     * @return string|null Version string if active and found, null otherwise.
     */
    public function get_main_zoho_plugin_version() {
        return $this->main_zoho_plugin_version;
    }

    /**
     * Attempts to retrieve API settings (like Client ID, Secret, Domain)
     * from the main Zoho plugin's stored options.
     *
     * Note: The option name and the structure of these settings are GUESSES
     * and need to be verified against the actual main plugin's implementation.
     *
     * @since 1.0.0
     * @return array|false An array of API settings if found, false otherwise.
     */
    public function get_main_zoho_plugin_api_credentials() {
        if ( ! $this->is_main_zoho_plugin_active() ) {
            return false;
        }

        // Retrieve the options where the main plugin might store its API credentials.
        $main_plugin_settings = get_option( self::MAIN_ZOHO_PLUGIN_GENERAL_SETTINGS_OPTION_NAME );

        if ( empty( $main_plugin_settings ) || ! is_array( $main_plugin_settings ) ) {
            wczb2b_log( 'Main Zoho plugin (' . self::MAIN_ZOHO_PLUGIN_PATH . ') is active, but its settings (' . self::MAIN_ZOHO_PLUGIN_GENERAL_SETTINGS_OPTION_NAME . ') are empty or not an array.', 'debug' );
            return false;
        }

        // GUESSING the keys within the main plugin's settings array.
        // These would typically be 'client_id', 'client_secret', 'domain' or similar.
        // We need to map them to a consistent format for our B2B plugin.
        $credentials = array();
        // Example hypothetical mapping:
        // if ( isset( $main_plugin_settings['zoho_client_id'] ) ) {
        //     $credentials['client_id'] = $main_plugin_settings['zoho_client_id'];
        // }
        // if ( isset( $main_plugin_settings['zoho_client_secret'] ) ) {
        //     $credentials['client_secret'] = $main_plugin_settings['zoho_client_secret'];
        // }
        // if ( isset( $main_plugin_settings['zoho_data_center'] ) ) { // e.g., 'com', 'eu'
        //     $credentials['domain'] = $main_plugin_settings['zoho_data_center'];
        // }

        // For now, return the raw array and let the calling class try to find the keys.
        // This is highly dependent on the main plugin's actual implementation.
        // If the structure is known, proper mapping should be done here.
        wczb2b_log( 'Main Zoho plugin API credential settings retrieved: ' . print_r( $main_plugin_settings, true ), 'debug' );
        return $main_plugin_settings; // Return the raw array for now
    }

    /**
     * Attempts to retrieve stored token details (access token, refresh token, expiry)
     * from the main Zoho plugin's stored options.
     *
     * Note: The option name and structure are GUESSES.
     *
     * @since 1.0.0
     * @return array|false An array of token details if found, false otherwise.
     */
    public function get_main_zoho_plugin_token_details() {
        if ( ! $this->is_main_zoho_plugin_active() ) {
            return false;
        }

        $token_details = get_option( self::MAIN_ZOHO_PLUGIN_TOKEN_OPTION_NAME );

        if ( empty( $token_details ) || ! is_array( $token_details ) ) {
            wczb2b_log( 'Main Zoho plugin (' . self::MAIN_ZOHO_PLUGIN_PATH . ') is active, but its token details (' . self::MAIN_ZOHO_PLUGIN_TOKEN_OPTION_NAME . ') are empty or not an array.', 'debug' );
            return false;
        }

        wczb2b_log( 'Main Zoho plugin token details retrieved: ' . print_r( $token_details, true ), 'debug' );
        return $token_details;
    }
}
?>
