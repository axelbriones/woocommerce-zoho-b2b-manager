<?php
/**
 * Auxiliary functions for the WooCommerce Zoho B2B Manager plugin.
 *
 * This file can be used for helper functions that don't neatly fit into a class
 * or are used across multiple classes.
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

/**
 * Helper function to check if the current user (or a specific user by ID)
 * has a designated B2B role.
 *
 * @since 1.0.0
 * @param int|null $user_id User ID to check. Defaults to current user.
 * @return bool True if user has a B2B role, false otherwise.
 */
function wczb2b_is_b2b_customer( $user_id = null ) {
    if ( is_null( $user_id ) ) {
        $user_id = get_current_user_id();
    }

    if ( ! $user_id ) {
        return false; // No user to check
    }

    $user = get_userdata( $user_id );
    if ( ! $user ) {
        return false;
    }

    // Get B2B roles from plugin settings or define them here.
    // Example: $b2b_roles = get_option( 'wc_zoho_b2b_approved_roles', array( 'b2b_customer_approved', 'wholesaler' ) );
    // For now, let's hardcode some common ones. These should match roles created in WC_Zoho_B2B_User_Manager
    $b2b_roles = apply_filters( 'wczb2b_get_b2b_user_roles', array(
        'b2b_customer_approved', // Example role defined in WC_Zoho_B2B_User_Manager
        // Add other roles like 'wholesaler', 'distributor' if you create them
    ) );


    if ( empty( $b2b_roles ) ) {
        return false; // No B2B roles defined
    }

    $user_roles = (array) $user->roles;

    foreach ( $b2b_roles as $b2b_role ) {
        if ( in_array( $b2b_role, $user_roles, true ) ) {
            return true;
        }
    }

    return false;
}

/**
 * Get a plugin option with a default value.
 *
 * @since 1.0.0
 * @param string $option_name The name of the option.
 * @param mixed  $default_value The default value if the option is not found.
 * @return mixed The option value.
 */
function wczb2b_get_option( $option_name, $default_value = '' ) {
    // Options for this plugin will likely be prefixed, e.g., 'wc_zoho_b2b_setting_name'
    return get_option( 'wc_zoho_b2b_' . $option_name, $default_value );
}

/**
 * Log messages for debugging or information.
 * Uses WooCommerce logger if available, otherwise error_log.
 *
 * @since 1.0.0
 * @param string $message Message to log.
 * @param string $level   Log level (e.g., 'info', 'debug', 'error').
 */
function wczb2b_log( $message, $level = 'info' ) {
    if ( class_exists( 'WC_Logger' ) && function_exists( 'wc_get_logger' ) ) {
        $logger = wc_get_logger();
        $context = array( 'source' => 'wc-zoho-b2b-manager' );
        switch ( $level ) {
            case 'debug':
                $logger->debug( $message, $context );
                break;
            case 'warning':
                $logger->warning( $message, $context );
                break;
            case 'error':
                $logger->error( $message, $context );
                break;
            case 'info':
            default:
                $logger->info( $message, $context );
                break;
        }
    } elseif ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
        error_log( '[' . strtoupper( $level ) . '] WooCommerce Zoho B2B Manager: ' . print_r( $message, true ) );
    }
}

// Add more global helper functions as needed.
