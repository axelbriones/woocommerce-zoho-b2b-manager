<?php
/**
 * Manages B2B wishlist functionality.
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

class WC_Zoho_B2B_Wishlist_Manager {

    private static $instance;
    private $table_wishlist_name;

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
        global $wpdb;
        $this->table_wishlist_name = $wpdb->prefix . 'wc_zoho_b2b_wishlist';

        // AJAX handlers for adding/removing items
        add_action( 'wp_ajax_wczb2b_add_to_wishlist', array( $this, 'ajax_handle_add_to_wishlist' ) );
        add_action( 'wp_ajax_nopriv_wczb2b_add_to_wishlist', array( $this, 'ajax_handle_add_to_wishlist' ) );

        add_action( 'wp_ajax_wczb2b_remove_from_wishlist', array( $this, 'ajax_handle_remove_from_wishlist' ) );
        add_action( 'wp_ajax_nopriv_wczb2b_remove_from_wishlist', array( $this, 'ajax_handle_remove_from_wishlist' ) );
    }

    /**
     * Check if wishlist functionality is enabled.
     * @return bool
     */
    public function is_enabled() {
        return 'yes' === get_option( 'wc_zoho_b2b_enable_wishlist', 'yes' );
    }

    /**
     * Check if guests are allowed to use the wishlist.
     * For now, we assume guests are not allowed, but this can be made an option.
     * @return bool
     */
    private function allow_guest_wishlist() {
        return apply_filters( 'wczb2b_allow_guest_wishlist', false );
    }

    /**
     * Add an item to the user's wishlist.
     *
     * @param int $user_id User ID (0 for guest if guest wishlists are supported).
     * @param int $product_id Product ID.
     * @param int $variation_id Variation ID (optional, defaults to 0).
     * @return bool|WP_Error True on success, WP_Error on failure or if item already exists.
     */
    public function add_item( $user_id, $product_id, $variation_id = 0 ) {
        if ( ! $this->is_enabled() ) {
            return new WP_Error( 'wishlist_disabled', __( 'Wishlist feature is currently disabled.', 'wc-zoho-b2b' ) );
        }
        if ( ! $user_id && ! $this->allow_guest_wishlist() ) {
            return new WP_Error( 'guest_wishlist_disabled', __( 'Please log in to add items to your wishlist.', 'wc-zoho-b2b' ) );
        }
        if ( ! $product_id || !get_post_status($product_id)) { // Also check if product exists
            return new WP_Error( 'invalid_product', __( 'Invalid product specified.', 'wc-zoho-b2b' ) );
        }

        global $wpdb;

        if ( $this->is_in_wishlist( $user_id, $product_id, $variation_id ) ) {
            return new WP_Error( 'already_in_wishlist', __( 'This product is already in your wishlist.', 'wc-zoho-b2b' ) );
        }

        $result = $wpdb->insert(
            $this->table_wishlist_name,
            array(
                'user_id'      => $user_id,
                'product_id'   => $product_id,
                'variation_id' => (int) $variation_id, // Ensure it's an int
                'date_added'   => current_time( 'mysql', true ),
            ),
            array( '%d', '%d', '%d', '%s' )
        );

        if ( $result ) {
            do_action( 'wczb2b_added_to_wishlist', $product_id, $variation_id, $user_id );
            return true;
        }
        wczb2b_log("Error adding item to wishlist. User: {$user_id}, Product: {$product_id}, Variation: {$variation_id}. DB Error: " . $wpdb->last_error, 'error');
        return new WP_Error( 'db_error', __( 'Could not add item to wishlist due to a database error.', 'wc-zoho-b2b' ) );
    }

    /**
     * Remove an item from the user's wishlist.
     *
     * @param int $user_id User ID.
     * @param int $product_id Product ID.
     * @param int $variation_id Variation ID (optional).
     * @return bool|WP_Error True on success, WP_Error on failure.
     */
    public function remove_item( $user_id, $product_id, $variation_id = 0 ) {
         if ( ! $this->is_enabled() ) {
            return new WP_Error( 'wishlist_disabled', __( 'Wishlist feature is currently disabled.', 'wc-zoho-b2b' ) );
        }
        if ( ! $user_id && ! $this->allow_guest_wishlist() ) {
            return new WP_Error( 'guest_wishlist_disabled', __( 'Login required to manage wishlist.', 'wc-zoho-b2b' ) );
        }

        global $wpdb;
        $deleted = $wpdb->delete(
            $this->table_wishlist_name,
            array(
                'user_id'      => $user_id,
                'product_id'   => $product_id,
                'variation_id' => (int) $variation_id,
            ),
            array( '%d', '%d', '%d' )
        );

        if ( $deleted !== false ) {
            do_action( 'wczb2b_removed_from_wishlist', $product_id, $variation_id, $user_id );
            return true;
        }
        wczb2b_log("Error removing item from wishlist. User: {$user_id}, Product: {$product_id}, Variation: {$variation_id}. DB Error: " . $wpdb->last_error, 'error');
        return new WP_Error( 'db_error', __( 'Could not remove item from wishlist due to a database error.', 'wc-zoho-b2b' ) );
    }

    /**
     * Get all wishlist items for a user.
     *
     * @param int $user_id User ID.
     * @return array Array of wishlist item objects (stdClass).
     */
    public function get_wishlist_items( $user_id ) {
        if ( ( ! $user_id && ! $this->allow_guest_wishlist() ) || !$this->is_enabled() ) {
            return array();
        }
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$this->table_wishlist_name} WHERE user_id = %d ORDER BY date_added DESC",
            $user_id
        ) );
    }

    /**
     * Check if a specific product (and variation) is in the user's wishlist.
     *
     * @param int $user_id User ID.
     * @param int $product_id Product ID.
     * @param int $variation_id Variation ID (optional).
     * @return bool True if in wishlist, false otherwise.
     */
    public function is_in_wishlist( $user_id, $product_id, $variation_id = 0 ) {
        if ( ( ! $user_id && ! $this->allow_guest_wishlist() ) || !$this->is_enabled() ) {
            return false;
        }
        global $wpdb;
        $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_wishlist_name} WHERE user_id = %d AND product_id = %d AND variation_id = %d",
            $user_id, $product_id, (int) $variation_id
        ) );
        return $count > 0;
    }

    /**
     * Count items in a user's wishlist.
     * @param int $user_id
     * @return int
     */
    public function count_items($user_id) {
        if ( ( ! $user_id && ! $this->allow_guest_wishlist() ) || !$this->is_enabled() ) {
            return 0;
        }
        global $wpdb;
        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_wishlist_name} WHERE user_id = %d",
            $user_id
        ) );
    }

    /**
     * AJAX handler to add a product to the wishlist.
     */
    public function ajax_handle_add_to_wishlist() {
        check_ajax_referer( 'wczb2b_wishlist_nonce', 'nonce' );

        $user_id = get_current_user_id();
        if ( ! $user_id && ! $this->allow_guest_wishlist() ) {
            wp_send_json_error( array( 'message' => __( 'Please log in to add items to your wishlist.', 'wc-zoho-b2b' ) ) );
        }

        $product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
        $variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;

        $result = $this->add_item( $user_id, $product_id, $variation_id );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        } else {
            wp_send_json_success( array(
                'message' => __( 'Product added to wishlist!', 'wc-zoho-b2b' ),
                'count' => $this->count_items($user_id),
                'button_html' => WC_Zoho_B2B_Frontend::get_instance()->get_wishlist_button_html($product_id, $variation_id)
            ) );
        }
    }

    /**
     * AJAX handler to remove a product from the wishlist.
     */
    public function ajax_handle_remove_from_wishlist() {
        check_ajax_referer( 'wczb2b_wishlist_nonce', 'nonce' );

        $user_id = get_current_user_id();
         if ( ! $user_id && ! $this->allow_guest_wishlist() ) {
            wp_send_json_error( array( 'message' => __( 'Please log in to manage your wishlist.', 'wc-zoho-b2b' ) ) );
        }

        $product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
        $variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;

        $result = $this->remove_item( $user_id, $product_id, $variation_id );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        } else {
             wp_send_json_success( array(
                'message' => __( 'Product removed from wishlist.', 'wc-zoho-b2b' ),
                'count' => $this->count_items($user_id),
                'button_html' => WC_Zoho_B2B_Frontend::get_instance()->get_wishlist_button_html($product_id, $variation_id)
            ) );
        }
    }
}
?>
