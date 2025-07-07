<?php
/**
 * Provides the view for the Zoho integration settings page/tab.
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

$zoho_integration = WC_Zoho_B2B_Zoho_Integration::get_instance(); // Get instance for helper methods
$compatibility = WC_Zoho_B2B_Compatibility::get_instance();

// Check OAuth status for display
$oauth_status = isset($_GET['oauth_status']) ? sanitize_text_field($_GET['oauth_status']) : null;
if ($oauth_status === 'success') {
    add_settings_error('wczb2b_zoho_notices', 'oauth_success', __('Zoho authorization successful! Access token has been saved.', 'wc-zoho-b2b'), 'updated');
} elseif ($oauth_status === 'failed') {
     add_settings_error('wczb2b_zoho_notices', 'oauth_failed', __('Zoho authorization failed. Please check logs or settings and try again.', 'wc-zoho-b2b'), 'error');
}

?>
<div class="wrap wczb2b-settings-wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <?php settings_errors('wczb2b_zoho_notices'); // Display custom notices for OAuth status ?>
    <?php settings_errors(); // Display general settings errors ?>

    <?php
    // Display information about using main plugin's configuration
    if ( $compatibility->is_main_zoho_plugin_active() ) {
        $use_main_config = get_option(WC_Zoho_B2B_Zoho_Integration::OPT_B2B_USE_MAIN_PLUGIN_CONFIG, 'yes');
        if ($use_main_config === 'yes') {
            echo '<div class="notice notice-info inline"><p>';
            echo wp_kses_post(
                sprintf(
                    /* translators: 1: Strong open tag, 2: Strong close tag, 3: Main plugin name */
                    __( '%1$sNote:%2$s You are currently configured to use API credentials from the main %3$s plugin. If that plugin is correctly authorized, B2B features should use its connection. You can disable this below to use separate credentials for B2B features.', 'wc-zoho-b2b' ),
                    '<strong>',
                    '</strong>',
                    esc_html(WC_Zoho_B2B_Compatibility::MAIN_ZOHO_PLUGIN_PATH) // Or a more user-friendly name
                )
            );
            echo '</p></div>';
        }
    }
    ?>

    <form method="post" action="options.php">
        <?php
        settings_fields( 'wczb2b_zoho_settings_group' );
        // Sections and fields for 'wczb2b-zoho-settings' page slug
        // will be defined in WC_Zoho_B2B_Admin::register_plugin_settings()
        ?>

        <h2><?php esc_html_e( 'Connection Settings', 'wc-zoho-b2b' ); ?></h2>
        <p><?php esc_html_e('Configure how this B2B plugin connects to Zoho. You can use the credentials from an existing Zoho integration plugin or define specific ones here.', 'wc-zoho-b2b'); ?></p>
        <table class="form-table">
            <?php if ( $compatibility->is_main_zoho_plugin_active() ) : ?>
            <tr valign="top">
                <th scope="row"><?php esc_html_e( 'Use Main Integration\'s Config', 'wc-zoho-b2b' ); ?></th>
                <td>
                    <label for="<?php echo esc_attr(WC_Zoho_B2B_Zoho_Integration::OPT_B2B_USE_MAIN_PLUGIN_CONFIG); ?>">
                        <input type="checkbox" id="<?php echo esc_attr(WC_Zoho_B2B_Zoho_Integration::OPT_B2B_USE_MAIN_PLUGIN_CONFIG); ?>"
                               name="<?php echo esc_attr(WC_Zoho_B2B_Zoho_Integration::OPT_B2B_USE_MAIN_PLUGIN_CONFIG); ?>"
                               value="yes" <?php checked( 'yes', get_option( WC_Zoho_B2B_Zoho_Integration::OPT_B2B_USE_MAIN_PLUGIN_CONFIG, 'yes' ) ); ?> />
                        <?php esc_html_e( 'Attempt to use API credentials and tokens from the main WooCommerce Zoho Integration plugin.', 'wc-zoho-b2b' ); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e( 'If checked, the settings below will only be used if the main plugin\'s configuration cannot be found or is incomplete for B2B needs.', 'wc-zoho-b2b' ); ?>
                    </p>
                </td>
            </tr>
            <?php endif; ?>

            <tr valign="top">
                <th scope="row"><label for="<?php echo esc_attr(WC_Zoho_B2B_Zoho_Integration::OPT_B2B_ZOHO_ENABLED); ?>"><?php esc_html_e( 'Enable B2B Zoho Features', 'wc-zoho-b2b' ); ?></label></th>
                <td>
                    <input type="checkbox" id="<?php echo esc_attr(WC_Zoho_B2B_Zoho_Integration::OPT_B2B_ZOHO_ENABLED); ?>"
                           name="<?php echo esc_attr(WC_Zoho_B2B_Zoho_Integration::OPT_B2B_ZOHO_ENABLED); ?>"
                           value="yes" <?php checked( 'yes', get_option( WC_Zoho_B2B_Zoho_Integration::OPT_B2B_ZOHO_ENABLED, 'no' ) ); ?> />
                    <label for="<?php echo esc_attr(WC_Zoho_B2B_Zoho_Integration::OPT_B2B_ZOHO_ENABLED); ?>"><?php esc_html_e( 'Enable synchronization and other Zoho-related features for B2B module.', 'wc-zoho-b2b' ); ?></label>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><label for="<?php echo esc_attr(WC_Zoho_B2B_Zoho_Integration::OPT_B2B_DOMAIN); ?>"><?php esc_html_e( 'Zoho Account Domain', 'wc-zoho-b2b' ); ?></label></th>
                <td>
                    <select id="<?php echo esc_attr(WC_Zoho_B2B_Zoho_Integration::OPT_B2B_DOMAIN); ?>" name="<?php echo esc_attr(WC_Zoho_B2B_Zoho_Integration::OPT_B2B_DOMAIN); ?>">
                        <?php $current_domain = get_option(WC_Zoho_B2B_Zoho_Integration::OPT_B2B_DOMAIN, 'com'); ?>
                        <option value="com" <?php selected($current_domain, 'com'); ?>>zoho.com (Global / US)</option>
                        <option value="eu" <?php selected($current_domain, 'eu'); ?>>zoho.eu (Europe)</option>
                        <option value="in" <?php selected($current_domain, 'in'); ?>>zoho.in (India)</option>
                        <option value="com.au" <?php selected($current_domain, 'com.au'); ?>>zoho.com.au (Australia)</option>
                        <option value="jp" <?php selected($current_domain, 'jp'); ?>>zoho.jp (Japan)</option>
                        <option value="ca" <?php selected($current_domain, 'ca'); ?>>zoho.ca (Canada)</option>
                        <option value="com.cn" <?php selected($current_domain, 'com.cn'); ?>>zoho.com.cn (China)</option>
                    </select>
                    <p class="description"><?php esc_html_e( 'Select the domain of your Zoho account. This determines the API endpoints.', 'wc-zoho-b2b' ); ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="<?php echo esc_attr(WC_Zoho_B2B_Zoho_Integration::OPT_B2B_CLIENT_ID); ?>"><?php esc_html_e( 'Client ID', 'wc-zoho-b2b' ); ?></label></th>
                <td><input type="text" id="<?php echo esc_attr(WC_Zoho_B2B_Zoho_Integration::OPT_B2B_CLIENT_ID); ?>" name="<?php echo esc_attr(WC_Zoho_B2B_Zoho_Integration::OPT_B2B_CLIENT_ID); ?>" value="<?php echo esc_attr( get_option(WC_Zoho_B2B_Zoho_Integration::OPT_B2B_CLIENT_ID) ); ?>" class="regular-text"/></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="<?php echo esc_attr(WC_Zoho_B2B_Zoho_Integration::OPT_B2B_CLIENT_SECRET); ?>"><?php esc_html_e( 'Client Secret', 'wc-zoho-b2b' ); ?></label></th>
                <td><input type="password" id="<?php echo esc_attr(WC_Zoho_B2B_Zoho_Integration::OPT_B2B_CLIENT_SECRET); ?>" name="<?php echo esc_attr(WC_Zoho_B2B_Zoho_Integration::OPT_B2B_CLIENT_SECRET); ?>" value="<?php echo esc_attr( get_option(WC_Zoho_B2B_Zoho_Integration::OPT_B2B_CLIENT_SECRET) ); ?>" class="regular-text"/></td>
            </tr>
             <tr valign="top">
                <th scope="row"><?php esc_html_e( 'Authorized Redirect URI', 'wc-zoho-b2b' ); ?></th>
                <td>
                    <input type="text" readonly value="<?php echo esc_url( $zoho_integration->get_redirect_uri() );?>" class="regular-text code"/>
                    <p class="description"><?php esc_html_e( 'Use this URI when creating your OAuth client in Zoho API Console.', 'wc-zoho-b2b' ); ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e( 'Authorization Status', 'wc-zoho-b2b' ); ?></th>
                <td>
                    <?php
                    // Ensure settings are loaded for current state
                    $zoho_integration->load_settings(); // Make sure we have the latest after potential save

                    if ( $zoho_integration->is_client_fully_configured() && $zoho_integration->has_valid_access_token(true) ) {
                        echo '<p style="color: green;"><strong>' . esc_html__( 'Successfully Authorized with Zoho.', 'wc-zoho-b2b' ) . '</strong></p>';
                        // Optionally show token expiry or link to re-authorize
                    } elseif ( $zoho_integration->is_client_fully_configured() && $zoho_integration->get_access_token() && !$zoho_integration->has_valid_access_token(true) ) {
                         echo '<p style="color: orange;"><strong>' . esc_html__( 'Token may have expired. Please re-authorize.', 'wc-zoho-b2b' ) . '</strong></p>';
                    } else {
                        echo '<p style="color: red;"><strong>' . esc_html__( 'Not Authorized.', 'wc-zoho-b2b' ) . '</strong></p>';
                    }

                    if ( $zoho_integration->is_client_fully_configured() && get_option(WC_Zoho_B2B_Zoho_Integration::OPT_B2B_ZOHO_ENABLED) === 'yes' ) {
                        $zoho_domain_selected = $zoho_integration->get_zoho_domain() ?: 'com'; // Default to .com if not set
                        $auth_url = "https://accounts.zoho.{$zoho_domain_selected}/oauth/v2/auth?" . http_build_query(array(
                            'scope' => 'ZohoCRM.modules.ALL,ZohoBooks.fullaccess.all,ZohoInventory.fullaccess.all', // Adjust scopes as needed
                            'client_id' => $zoho_integration->get_client_id(),
                            'response_type' => 'code',
                            'redirect_uri' => $zoho_integration->get_redirect_uri(),
                            'access_type' => 'offline', // To get refresh_token
                            // 'state' => wp_create_nonce('wczb2b_zoho_oauth_state') // Optional: for CSRF protection
                        ));
                        echo '<a href="' . esc_url( $auth_url ) . '" class="button button-primary">' . esc_html__( 'Authorize with Zoho', 'wc-zoho-b2b' ) . '</a>';
                        echo '<p class="description">' . esc_html__('Click to start the OAuth 2.0 authorization process with Zoho. Ensure Client ID, Secret, Domain and Redirect URI are correctly configured and saved first.', 'wc-zoho-b2b') . '</p>';

                    } elseif (get_option(WC_Zoho_B2B_Zoho_Integration::OPT_B2B_ZOHO_ENABLED) !== 'yes') {
                        echo '<p class="description">' . esc_html__('Enable B2B Zoho Features and save settings to proceed with authorization.', 'wc-zoho-b2b') . '</p>';
                    } else {
                         echo '<p class="description">' . esc_html__('Please fill in and save Client ID, Client Secret, and Zoho Domain to enable authorization.', 'wc-zoho-b2b') . '</p>';
                    }
                    ?>
                </td>
            </tr>
        </table>

        <h2><?php esc_html_e( 'Synchronization Settings', 'wc-zoho-b2b' ); ?></h2>
        <table class="form-table">
             <tr valign="top">
                <th scope="row"><label for="wc_zoho_b2b_zoho_sync_users"><?php esc_html_e( 'Sync B2B Users/Contacts', 'wc-zoho-b2b' ); ?></label></th>
                <td>
                    <input type="checkbox" id="wc_zoho_b2b_zoho_sync_users" name="wc_zoho_b2b_zoho_sync_users" value="yes" <?php checked( 'yes', get_option( 'wc_zoho_b2b_zoho_sync_users', 'no' ) ); ?> />
                    <label for="wc_zoho_b2b_zoho_sync_users"><?php esc_html_e( 'Enable syncing new B2B users/applications to Zoho CRM as Contacts/Accounts.', 'wc-zoho-b2b' ); ?></label>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wc_zoho_b2b_zoho_sync_pricing"><?php esc_html_e( 'Sync B2B Pricing', 'wc-zoho-b2b' ); ?></label></th>
                <td>
                    <input type="checkbox" id="wc_zoho_b2b_zoho_sync_pricing" name="wc_zoho_b2b_zoho_sync_pricing" value="yes" <?php checked( 'yes', get_option( 'wc_zoho_b2b_zoho_sync_pricing', 'no' ) ); ?> />
                    <label for="wc_zoho_b2b_zoho_sync_pricing"><?php esc_html_e( 'Enable syncing B2B pricing rules with Zoho (e.g., Price Lists in Zoho Inventory/Books). (Advanced)', 'wc-zoho-b2b' ); ?></label>
                </td>
            </tr>
            <?php // More settings sections and fields can be added via do_settings_sections('wczb2b-zoho-settings-sync'); for example ?>
        </table>

        <?php
        // This call will render all sections and fields added to the 'wczb2b-zoho-settings' page.
        // For better organization, you might create separate do_settings_sections calls for different parts of this page.
        // For example, one for connection, one for sync options.
        // However, all fields here are registered to 'wczb2b_zoho_settings_group'.
        // do_settings_sections( 'wczb2b-zoho-settings' );
        ?>

        <?php submit_button( __( 'Save Zoho Settings', 'wc-zoho-b2b' ) ); ?>
    </form>
</div>
