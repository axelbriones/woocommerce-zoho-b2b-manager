<?php
/**
 * Manages B2B specific order functionalities.
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

class WC_Zoho_B2B_Order_Manager {

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        // Hooks related to order creation, processing, and display for B2B customers.
        // Example: add_action( 'woocommerce_checkout_create_order', array( $this, 'add_b2b_order_meta' ), 10, 2 );
        // Example: add_filter( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'display_b2b_order_meta_admin' ), 10, 1 );
    }

    /**
     * Add custom meta to B2B orders.
     *
     * @since 1.0.0
     * @param WC_Order $order The order object.
     * @param array    $data  The data posted from the checkout form.
     */
    // public function add_b2b_order_meta( $order, $data ) {
    //     if ( $this->is_b2b_customer( $order->get_user_id() ) ) {
    //         // Example: Add a flag indicating this is a B2B order
    //         $order->update_meta_data( '_is_wczb2b_order', 'yes' );
    //         // Add other B2B specific meta data if needed, e.g., Purchase Order Number
    //         // if ( ! empty( $data['purchase_order_number'] ) ) {
    //         //    $order->update_meta_data( '_wczb2b_po_number', sanitize_text_field( $data['purchase_order_number'] ) );
    //         // }
    //         $order->save();
    //     }
    // }

    /**
     * Display B2B custom meta in the admin order view.
     *
     * @since 1.0.0
     * @param WC_Order $order The order object.
     */
    // public function display_b2b_order_meta_admin( $order ) {
    //     $is_b2b = $order->get_meta( '_is_wczb2b_order' );
    //     if ( 'yes' === $is_b2b ) {
    //         echo '<p><strong>' . esc_html__( 'Order Type:', 'wc-zoho-b2b' ) . '</strong> ' . esc_html__( 'B2B Order', 'wc-zoho-b2b' ) . '</p>';
    //         // $po_number = $order->get_meta( '_wczb2b_po_number' );
    //         // if ( $po_number ) {
    //         //     echo '<p><strong>' . esc_html__( 'PO Number:', 'wc-zoho-b2b' ) . '</strong> ' . esc_html( $po_number ) . '</p>';
    //         // }
    //     }
    // }

    /**
     * Helper function to check if a user is a B2B customer.
     *
     * @since 1.0.0
     * @param int $user_id The user ID.
     * @return bool
     */
    // private function is_b2b_customer( $user_id ) {
    //     if ( ! $user_id ) {
    //         return false;
    //     }
    //     $user = get_user_by( 'id', $user_id );
    //     if ( ! $user ) {
    //         return false;
    //     }
    //     $b2b_roles = array( 'b2b_customer_approved', 'b2b_wholesaler' ); // Define your B2B roles
    //     $user_roles = (array) $user->roles;
    //     foreach ( $b2b_roles as $b2b_role ) {
    //         if ( in_array( $b2b_role, $user_roles, true ) ) {
    //             return true;
    //         }
    //     }
    //     return false;
    // }

    // More methods for order processing rules, payment gateway adjustments for B2B, etc.
}

// new WC_Zoho_B2B_Order_Manager();
