<?php
/**
 * Manages integration and synchronization with Zoho services for B2B features.
 * This class will need to coordinate with the existing WooCommerce Zoho Integration plugin.
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

class WC_Zoho_B2B_Zoho_Integration {

    private static $instance;

    // Store resolved API credentials
    private $client_id;
    private $client_secret;
    private $zoho_domain; // e.g., 'com', 'eu', 'in' (the TLD part of zohoapis.com, zohoapis.eu etc.)
    private $access_token;
    private $refresh_token;
    private $token_expires_at; // Timestamp

    // Option names for this plugin's own Zoho settings
    const OPT_B2B_CLIENT_ID                 = 'wczb2b_client_id';
    const OPT_B2B_CLIENT_SECRET             = 'wczb2b_client_secret';
    const OPT_B2B_DOMAIN                    = 'wczb2b_zoho_domain';
    const OPT_B2B_ACCESS_TOKEN              = 'wczb2b_access_token';
    const OPT_B2B_REFRESH_TOKEN             = 'wczb2b_refresh_token';
    const OPT_B2B_TOKEN_EXPIRES_AT          = 'wczb2b_token_expires_at';
    const OPT_B2B_USE_MAIN_PLUGIN_CONFIG    = 'wczb2b_use_main_plugin_config'; // 'yes' or 'no'
    const OPT_B2B_ZOHO_ENABLED              = 'wczb2b_zoho_enabled'; // General toggle for B2B Zoho features
    const OPT_B2B_ZOHO_SYNC_USERS           = 'wc_zoho_b2b_zoho_sync_users'; // Option for enabling user sync

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->load_settings();

        // Hook for syncing B2B user approved
        add_action( 'wczb2b_user_approved', array( $this, 'schedule_user_sync_to_zoho' ), 10, 3 );
        // add_action( 'woocommerce_thankyou', array( $this, 'handle_order_sync' ), 10, 1 );
    }

    /**
     * Load Zoho API settings.
     */
    public function load_settings() {
        $compatibility = WC_Zoho_B2B_Compatibility::get_instance();
        $use_main_plugin_config_opt = get_option( self::OPT_B2B_USE_MAIN_PLUGIN_CONFIG, 'yes' );
        $use_main_plugin_config = ( 'yes' === $use_main_plugin_config_opt );

        $main_plugin_credentials_loaded = false;
        $main_plugin_tokens_loaded = false;

        if ( $use_main_plugin_config && $compatibility->is_main_zoho_plugin_active() ) {
            wczb2b_log( 'Attempting to load Zoho config from main integration plugin.', 'info' );
            $main_creds = $compatibility->get_main_zoho_plugin_api_credentials(); // This is an array of options from main plugin
            $main_tokens = $compatibility->get_main_zoho_plugin_token_details(); // This is an array of options from main plugin

            if ( $main_creds && is_array( $main_creds ) ) {
                // GUESSING keys from the main plugin's options. These MUST be verified.
                // Example: if main plugin stores as 'zoho_client_id', 'api_client_id' etc.
                // Prioritize direct key names, then try common variations.
                $this->client_id     = $main_creds['client_id'] ?? $main_creds['api_client_id'] ?? $main_creds['zoho_client_id'] ?? null;
                $this->client_secret = $main_creds['client_secret'] ?? $main_creds['api_client_secret'] ?? $main_creds['zoho_client_secret'] ?? null;
                $this->zoho_domain   = $main_creds['domain'] ?? $main_creds['data_center'] ?? $main_creds['api_domain'] ?? $main_creds['zoho_domain'] ?? null;

                if ($this->client_id && $this->client_secret && $this->zoho_domain) {
                    $main_plugin_credentials_loaded = true;
                    wczb2b_log( 'Loaded credentials from main plugin. Client ID set: ' . !empty($this->client_id), 'debug' );
                } else {
                    wczb2b_log( 'Main plugin credentials found, but key names did not match expected patterns (client_id, client_secret, domain/data_center). Main creds dump: ' . print_r($main_creds, true), 'debug' );
                }
            }

            if ( $main_tokens && is_array( $main_tokens ) ) {
                $this->access_token  = $main_tokens['access_token'] ?? null;
                $this->refresh_token = $main_tokens['refresh_token'] ?? null;
                $expires_in = $main_tokens['expires_in'] ?? null;
                $expiry_time = $main_tokens['expiry_time'] ?? $main_tokens['token_expires_at'] ?? null;

                if ($expires_in && !$expiry_time) {
                    $this->token_expires_at = time() + (int)$expires_in;
                } elseif ($expiry_time) {
                    $this->token_expires_at = (int)$expiry_time;
                } else {
                    $this->token_expires_at = null;
                }

                if ($this->access_token) {
                    $main_plugin_tokens_loaded = true;
                     wczb2b_log( 'Loaded tokens from main plugin. Access token set: ' . !empty($this->access_token), 'debug' );
                } else {
                    wczb2b_log( 'Main plugin tokens found, but access_token key not found. Main tokens dump: ' . print_r($main_tokens, true), 'debug' );
                }
            }
        }

        if ( ! $use_main_plugin_config || ! $main_plugin_credentials_loaded ) {
            if ( $use_main_plugin_config && ! $main_plugin_credentials_loaded ) {
                 wczb2b_log( 'Main plugin config selected, but essential credentials missing/unmappable from it. Falling back to B2B plugin settings for credentials.', 'info' );
            } else if (!$use_main_plugin_config) {
                 wczb2b_log( 'Loading Zoho credentials from B2B plugin specific settings.', 'info' );
            }
            $this->client_id     = get_option( self::OPT_B2B_CLIENT_ID );
            $this->client_secret = get_option( self::OPT_B2B_CLIENT_SECRET );
            $this->zoho_domain   = get_option( self::OPT_B2B_DOMAIN );
        }

        if ( ! $use_main_plugin_config || ! $main_plugin_tokens_loaded ) {
             if ( $use_main_plugin_config && ! $main_plugin_tokens_loaded ) {
                wczb2b_log( 'Main plugin config selected, but tokens missing/unmappable from it. Falling back to B2B plugin settings for tokens.', 'info' );
            } else if (!$use_main_plugin_config) {
                wczb2b_log( 'Loading Zoho tokens from B2B plugin specific settings.', 'info' );
            }
            $this->access_token  = get_option( self::OPT_B2B_ACCESS_TOKEN );
            $this->refresh_token = get_option( self::OPT_B2B_REFRESH_TOKEN );
            $this->token_expires_at = get_option( self::OPT_B2B_TOKEN_EXPIRES_AT );
        }
    }

    public function get_client_id() { return $this->client_id; }
    public function get_client_secret() { return $this->client_secret; }
    public function get_zoho_domain() { return $this->zoho_domain; }
    public function get_access_token() { return $this->access_token; }

    public function is_b2b_zoho_enabled() {
        return 'yes' === get_option(self::OPT_B2B_ZOHO_ENABLED, 'no');
    }

    public function is_client_fully_configured() {
        return $this->is_b2b_zoho_enabled() && !empty( $this->client_id ) && !empty( $this->client_secret ) && !empty( $this->zoho_domain );
    }

    public function has_valid_access_token( $check_expiry = true ) {
        if ( empty( $this->access_token ) ) {
            return false;
        }
        if ( !$check_expiry ) {
            return true;
        }
        if ( empty( $this->token_expires_at ) ) {
            wczb2b_log("Access token exists but its expiry time is unknown. Assuming it might be valid.", "warning");
            return true;
        }
        return time() < ( (int) $this->token_expires_at - 60 );
    }

    public function update_tokens( $access_token, $expires_in, $refresh_token = null ) {
        $this->access_token  = $access_token;
        $this->token_expires_at = time() + (int) $expires_in;

        update_option( self::OPT_B2B_ACCESS_TOKEN, $this->access_token );
        update_option( self::OPT_B2B_TOKEN_EXPIRES_AT, $this->token_expires_at );

        if ( $refresh_token ) {
            $this->refresh_token = $refresh_token;
            update_option( self::OPT_B2B_REFRESH_TOKEN, $this->refresh_token );
        }
        wczb2b_log( 'Zoho tokens updated successfully for B2B plugin.', 'info' );
    }

    public function get_redirect_uri(){
        return admin_url( 'admin.php?page=wczb2b-zoho-settings&action=wczb2b_oauth_callback' );
    }

    public function get_api_base_url($service = 'crm', $version = 'v2') {
        if (empty($this->zoho_domain)) {
            wczb2b_log("Zoho domain/data center not set. Cannot determine API base URL.", "error");
            return false;
        }
        $base_urls = [
            'crm'       => "https://www.zohoapis.{$this->zoho_domain}/crm/{$version}/",
            'books'     => "https://books.zohoapis.{$this->zoho_domain}/books/v3/",
            'inventory' => "https://inventory.zohoapis.{$this->zoho_domain}/api/v1/",
        ];
        return isset($base_urls[$service]) ? $base_urls[$service] : false;
    }

    public function refresh_access_token() {
        if ( ! $this->is_client_fully_configured() || empty( $this->refresh_token ) ) {
            wczb2b_log( 'Cannot refresh token: Client not fully configured or no refresh token.', 'error' );
            return false;
        }
        wczb2b_log( 'Attempting to refresh Zoho access token...', 'info' );

        $token_url = "https://accounts.zoho.{$this->zoho_domain}/oauth/v2/token";
        $response = wp_remote_post( $token_url, array(
            'method'    => 'POST',
            'timeout'   => 45,
            'body'      => array(
                'refresh_token'  => $this->refresh_token,
                'client_id'      => $this->client_id,
                'client_secret'  => $this->client_secret,
                'grant_type'     => 'refresh_token',
            ),
        ));

        if ( is_wp_error( $response ) ) {
            wczb2b_log('Zoho token refresh WP_Error: ' . $response->get_error_message(), 'error');
            return false;
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        $response_body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $response_code === 200 && isset( $response_body['access_token'] ) && isset($response_body['expires_in']) ) {
            // Note: Zoho refresh token grant might not return a new refresh_token.
            // Only update refresh token if it's explicitly provided in the response.
            $new_refresh_token = isset($response_body['refresh_token']) ? $response_body['refresh_token'] : $this->refresh_token;
            $this->update_tokens( $response_body['access_token'], $response_body['expires_in'], $new_refresh_token );
            wczb2b_log('Zoho access token refreshed successfully.', 'info');
            return true;
        } else {
            $error_message = isset($response_body['error']) ? $response_body['error'] : 'Unknown error during token refresh.';
            wczb2b_log('Zoho token refresh failed. RC: ' . $response_code . ' Error: ' . $error_message . ' Body: ' . print_r($response_body, true), 'error');
            // If refresh token is invalid (e.g., revoked), clear it to prevent further failed attempts.
            if (in_array($error_message, ['invalid_code', 'invalid_client', 'invalid_token'])) { // Common OAuth errors for invalid refresh tokens
                delete_option(self::OPT_B2B_REFRESH_TOKEN);
                delete_option(self::OPT_B2B_ACCESS_TOKEN);
                delete_option(self::OPT_B2B_TOKEN_EXPIRES_AT);
                $this->refresh_token = null;
                $this->access_token = null;
                $this->token_expires_at = null;
                 wczb2b_log('Cleared invalid Zoho refresh token due to refresh error: ' . $error_message, 'warning');
            }
            return false;
        }
    }

    public function make_api_request( $service, $endpoint, $method = 'GET', $data = array(), $headers = array() ) {
        if ( ! $this->is_client_fully_configured() ) {
            wczb2b_log( "Zoho API client not configured. Cannot make request to {$service}/{$endpoint}.", 'error' );
            return new WP_Error('client_not_configured', 'Zoho API client is not configured.');
        }

        if ( ! $this->has_valid_access_token() ) {
            if ( ! $this->refresh_access_token() ) {
                 wczb2b_log( "Zoho access token is invalid/expired and refresh failed. Cannot make request to {$service}/{$endpoint}.", 'error' );
                 return new WP_Error('token_error', 'Zoho access token is invalid and refresh failed.');
            }
        }

        $base_url = $this->get_api_base_url($service);
        if (!$base_url) {
            wczb2b_log( "Could not determine base URL for service: {$service}", 'error' );
            return new WP_Error('base_url_error', "Could not determine base URL for service: {$service}");
        }
        $api_url = $base_url . ltrim($endpoint, '/'); // Ensure no double slashes

        $default_headers = array(
            'Authorization' => 'Zoho-oauthtoken ' . $this->access_token,
        );
        // Only add Content-Type if there's a body (for POST/PUT)
        if (strtoupper($method) === 'POST' || strtoupper($method) === 'PUT') {
            $default_headers['Content-Type'] = 'application/json;charset=UTF-8';
        }

        $request_headers = array_merge($default_headers, $headers);

        $request_args = array(
            'method'    => strtoupper($method),
            'headers'   => $request_headers,
            'timeout'   => 45, // Increased timeout
        );

        if ( (strtoupper($method) === 'POST' || strtoupper($method) === 'PUT') && !empty($data) ) {
            $request_args['body'] = json_encode( $data );
        }

        wczb2b_log( "Making API {$method} request to Zoho: {$api_url}", 'debug' );
        // wczb2b_log( "Request Args: " . print_r( $request_args, true ), 'debug' ); // Be careful logging body with sensitive data

        $response = wp_remote_request( $api_url, $request_args );

        if ( is_wp_error( $response ) ) {
           wczb2b_log( "Zoho API request WP_Error for {$api_url}: " . $response->get_error_message(), 'error' );
        } else {
            $response_code = wp_remote_retrieve_response_code( $response );
            // Log non-2xx responses for easier debugging
            if ( $response_code < 200 || $response_code >= 300 ) {
                 wczb2b_log( "Zoho API request to {$api_url} returned HTTP {$response_code}. Body: " . wp_remote_retrieve_body( $response ), 'warning' );
            }
        }
        return $response;
    }

    /**
     * Schedules a background task to sync user data to Zoho.
     *
     * @param int    $user_id          WordPress User ID.
     * @param int    $application_id   Application ID.
     * @param object $application_data Application data object.
     */
    public function schedule_user_sync_to_zoho( $user_id, $application_id, $application_data ) {
        if ( ! $this->is_b2b_zoho_enabled() || 'yes' !== get_option(self::OPT_B2B_ZOHO_SYNC_USERS, 'no') ) {
            wczb2b_log("Zoho sync for B2B users is disabled. Skipping sync for user #{$user_id}.", 'info');
            return;
        }
        wczb2b_log("Scheduling/triggering sync for approved B2B user #{$user_id} from application #{$application_id}.", 'info');
        // For now, direct call. TODO: Implement background processing (WP Cron or Action Scheduler)
        $this->sync_b2b_user_to_zoho_crm($user_id, $application_data);
    }

    /**
     * Syncs an approved B2B user/company to Zoho CRM as a Contact/Account.
     *
     * @param int    $user_id WordPress User ID.
     * @param object $application Application data object from wc_zoho_b2b_applications table.
     * @return bool True on success, false on failure.
     */
    public function sync_b2b_user_to_zoho_crm( $user_id, $application ) {
        if ( ! $this->is_client_fully_configured() ) { // Checks enabled flag too
            wczb2b_log( "Zoho API not configured or B2B Zoho features disabled. Cannot sync user #{$user_id}.", 'error' );
            return false;
        }

        $wp_user = get_userdata( $user_id );
        if ( ! $wp_user ) {
            wczb2b_log( "Invalid user ID #{$user_id} for Zoho sync.", 'error' );
            return false;
        }

        $b2b_zoho_contact_id = get_user_meta( $user_id, '_wczb2b_zoho_contact_id', true );
        $b2b_zoho_account_id = get_user_meta( $user_id, '_wczb2b_zoho_account_id', true );

        if ( $b2b_zoho_contact_id && $b2b_zoho_account_id ) {
            wczb2b_log( "User #{$user_id} already has B2B Zoho IDs (Contact: {$b2b_zoho_contact_id}, Account: {$b2b_zoho_account_id}). TODO: Implement update logic if needed.", 'info' );
            return true;
        }

        $main_plugin_zoho_contact_id = null;
        if (WC_Zoho_B2B_Compatibility::get_instance()->is_main_zoho_plugin_active()) {
            $main_plugin_zoho_contact_id = get_user_meta( $user_id, '_wzc_crm_contact_id', true ); // GUESS for main plugin's meta_key
            if ($main_plugin_zoho_contact_id) {
                 wczb2b_log( "User #{$user_id} appears to be already synced by the main Zoho plugin (Contact ID: {$main_plugin_zoho_contact_id}). Linking B2B application.", 'info' );
                 global $wpdb;
                 $table_name_applications = $wpdb->prefix . 'wc_zoho_b2b_applications';
                 $wpdb->update( $table_name_applications, array('zoho_contact_id' => $main_plugin_zoho_contact_id), array('id' => $application->id), array('%s'), array('%d') );
                 update_user_meta( $user_id, '_wczb2b_zoho_contact_id', $main_plugin_zoho_contact_id );
                 // Assume Account is also handled by main plugin in this case.
                 return true;
            }
        }

        // --- Sync Account (Company) ---
        $account_payload = array( 'data' => array(array(
            'Account_Name' => $application->company_name,
            'Phone'        => $application->phone ?: get_user_meta($user_id, 'billing_phone', true),
            // Add more fields: Website, Billing Address fields from user meta if available
            // 'Website'        => $wp_user->user_url,
            // 'Billing_Street' => get_user_meta($user_id, 'billing_address_1', true),
        )));
        if ($application->tax_id) {
            // $account_payload['data'][0]['Tax_ID_Field_API_Name'] = $application->tax_id; // Replace with actual API name
        }

        wczb2b_log("Attempting to sync Account to Zoho: " . print_r($account_payload, true), 'debug');
        $account_response = $this->make_api_request('crm', 'Accounts', 'POST', $account_payload );

        $zoho_account_id = null;
        if ( !is_wp_error($account_response) && wp_remote_retrieve_response_code($account_response) < 300 ) {
            $account_response_body = json_decode(wp_remote_retrieve_body($account_response), true);
            if ( isset($account_response_body['data'][0]['code']) && $account_response_body['data'][0]['code'] === 'SUCCESS' ) {
                $zoho_account_id = $account_response_body['data'][0]['details']['id'];
                wczb2b_log( "Zoho Account created/updated for user #{$user_id}. Zoho Account ID: {$zoho_account_id}", 'info' );
                update_user_meta( $user_id, '_wczb2b_zoho_account_id', $zoho_account_id );
            } else {
                wczb2b_log( "Failed to create/update Zoho Account for user #{$user_id}. Response: " . print_r($account_response_body, true), 'error' );
                return false; // Stop if account creation failed
            }
        } else {
             wczb2b_log( "Error response or WP_Error when creating/updating Zoho Account for user #{$user_id}. Response: " . print_r($account_response, true), 'error' );
            return false; // Stop if account creation failed
        }

        // --- Sync Contact ---
        $contact_last_name = $wp_user->last_name;
        $contact_first_name = $wp_user->first_name;

        if (empty($contact_last_name) && !empty($application->contact_person)) {
            $name_parts = explode(' ', $application->contact_person, 2);
            $contact_first_name = $name_parts[0];
            $contact_last_name = isset($name_parts[1]) ? $name_parts[1] : $name_parts[0]; // If only one name, use it as last name
        } elseif (empty($contact_last_name)) {
            $contact_last_name = $application->company_name; // Fallback if no other name available
        }

        $contact_payload = array( 'data' => array(array(
            'Account_Name' => array('id' => $zoho_account_id), // Link to the Account
            'Last_Name'    => $contact_last_name,
            'First_Name'   => $contact_first_name,
            'Email'        => $wp_user->user_email,
            'Phone'        => $application->phone ?: get_user_meta($user_id, 'billing_phone', true),
        )));

        wczb2b_log("Attempting to sync Contact to Zoho: " . print_r($contact_payload, true), 'debug');
        $contact_response = $this->make_api_request('crm', 'Contacts', 'POST', $contact_payload );

        if ( !is_wp_error($contact_response) && wp_remote_retrieve_response_code($contact_response) < 300 ) {
            $contact_response_body = json_decode(wp_remote_retrieve_body($contact_response), true);
            if ( isset($contact_response_body['data'][0]['code']) && $contact_response_body['data'][0]['code'] === 'SUCCESS' ) {
                $zoho_contact_id = $contact_response_body['data'][0]['details']['id'];
                wczb2b_log( "Zoho Contact created/updated for user #{$user_id}. Zoho Contact ID: {$zoho_contact_id}", 'info' );
                update_user_meta( $user_id, '_wczb2b_zoho_contact_id', $zoho_contact_id );

                global $wpdb;
                $table_name_applications = $wpdb->prefix . 'wc_zoho_b2b_applications';
                $wpdb->update( $table_name_applications, array('zoho_contact_id' => $zoho_contact_id), array('id' => $application->id), array('%s'), array('%d') );
                return true;
            } else {
                wczb2b_log( "Failed to create/update Zoho Contact for user #{$user_id}. Response: " . print_r($contact_response_body, true), 'error' );
            }
        } else {
            wczb2b_log( "Error response or WP_Error when creating/updating Zoho Contact for user #{$user_id}. Response: " . print_r($contact_response, true), 'error' );
        }
        return false;
    }
}
?>
