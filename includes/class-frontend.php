<?php
/**
 * Frontend functionality for the plugin.
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

class WC_Zoho_B2B_Frontend {

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    private static $instance;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        // Add hooks for frontend functionality here
        add_shortcode( 'wc_zoho_b2b_application_form', array( $this, 'render_application_form_shortcode' ) );
        add_shortcode( 'wc_zoho_b2b_wishlist', array( $this, 'render_wishlist_shortcode' ) );
        // add_shortcode( 'wc_zoho_b2b_my_account', array( $this, 'render_my_account_b2b_shortcode' ) );

        // Wishlist button on product pages
        add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'display_wishlist_button_product_page' ), 20 );
        // For archives, a bit more complex, might need to hook into loop item templates
        // add_action( 'woocommerce_after_shop_loop_item', array( $this, 'display_wishlist_button_product_loop' ), 15 );


        // Hook to display notices on the application form page or other relevant pages
        // add_action( 'wp', array( $this, 'maybe_display_notices' ) ); // Using wp to ensure notices are set before headers
    }

    /**
     * Render the B2B application form shortcode.
     *
     * @since    1.0.0
     * @param array $atts Shortcode attributes.
     * @return string Shortcode output.
     */
    public function render_application_form_shortcode( $atts ) {
        $user_manager = WC_Zoho_B2B_User_Manager::get_instance();
        $output = '';

        // Handle conditions for displaying the form
        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();
            // Check if user is already an approved B2B customer
            if ( wczb2b_is_b2b_customer( $user_id ) ) { // wczb2b_is_b2b_customer uses defined approved roles
                return '<div class="woocommerce-message">' . esc_html__( 'You are already an approved B2B customer.', 'wc-zoho-b2b' ) . '</div>';
            }
            // Check if user has a PENDING application
            // This requires a method in User_Manager to get application by user_id and status
            // $pending_application = $user_manager->get_user_application_by_status( $user_id, 'pending' );
            // if ( $pending_application ) {
            //     return '<div class="woocommerce-info">' . esc_html__( 'Your B2B application is currently pending review.', 'wc-zoho-b2b' ) . '</div>';
            // }
        } else {
            // For guest users, check if an application with their submitted email is pending
            // This would require checking after form submission attempt if email matches a pending app.
            // For now, guests can always see the form if not logged in.
        }


        ob_start();

        // Display messages passed via transients (e.g., after form submission)
        if ( $message = get_transient( 'wczb2b_application_message' ) ) {
            $message_type = get_transient( 'wczb2b_application_message_type' ); // 'success' or 'error'
            // Use WooCommerce notice types for styling consistency if preferred
            $notice_class = ($message_type === 'success') ? 'woocommerce-message' : 'woocommerce-error';
            if ($message_type === 'info') $notice_class = 'woocommerce-info';

            echo '<div class="' . esc_attr( $notice_class ) . '" role="alert">' . wp_kses_post( $message ) . '</div>';

            delete_transient( 'wczb2b_application_message' );
            delete_transient( 'wczb2b_application_message_type' );
        }

        // If a successful submission query arg is present, show a generic success (if transient was missed/cleared)
        if (isset($_GET['application_submitted']) && $_GET['application_submitted'] === 'success' && !get_transient('wczb2b_application_message')) {
             echo '<div class="woocommerce-message" role="alert">' . esc_html__('Thank you for your application! We will review it shortly.', 'wc-zoho-b2b') . '</div>';
        }


        require WCZB2B_PLUGIN_DIR . 'public/partials/application-form.php';
        return ob_get_clean();
    }

    /**
     * Render the B2B Wishlist shortcode.
     *
     * @since    1.0.0
     * @param array $atts Shortcode attributes.
     * @return string Shortcode output.
     */
    public function render_wishlist_shortcode( $atts ) {
        $wishlist_manager = WC_Zoho_B2B_Wishlist_Manager::get_instance();
        if ( ! $wishlist_manager->is_enabled() ) {
            return '<!-- Wishlist is disabled -->';
        }

        $user_id = get_current_user_id();
        if ( ! $user_id && ! apply_filters( 'wczb2b_allow_guest_wishlist', false ) ) {
            return '<div class="woocommerce-info">' . sprintf(
                __( 'Please %slog in%s to view your wishlist.', 'wc-zoho-b2b' ),
                '<a href="' . esc_url( wc_get_page_permalink( 'myaccount' ) ) . '">',
                '</a>'
            ) . '</div>';
        }

        ob_start();
        // Pass items to the template
        $wishlist_items = $wishlist_manager->get_wishlist_items( $user_id );
        wc_get_template(
            'wishlist.php',
            array( 'wishlist_items' => $wishlist_items ),
            'woocommerce-zoho-b2b-manager/', // Template path prefix in theme
            WCZB2B_PLUGIN_DIR . 'public/partials/' // Default plugin path
        );
        return ob_get_clean();
    }

    /**
     * Display the "Add to Wishlist" button on single product pages.
     */
    public function display_wishlist_button_product_page() {
        global $product;
        if ( ! $product ) return;

        $wishlist_manager = WC_Zoho_B2B_Wishlist_Manager::get_instance();
        if ( ! $wishlist_manager->is_enabled() ) return;

        // Don't show for guests if guest wishlists are not allowed
        if ( !get_current_user_id() && !apply_filters( 'wczb2b_allow_guest_wishlist', false ) && !is_user_logged_in() ) {
            // Optionally, show a "Login to add to wishlist" message/link
            // echo '<p class="wczb2b-wishlist-login-required"><a href="' . esc_url(wc_get_page_permalink('myaccount')) . '">' . __('Login to add to wishlist', 'wc-zoho-b2b') . '</a></p>';
            return;
        }

        echo $this->get_wishlist_button_html( $product->get_id() );
    }

    /**
     * Generates the HTML for the wishlist button.
     *
     * @param int $product_id
     * @param int $variation_id (optional)
     * @return string Button HTML.
     */
    public function get_wishlist_button_html( $product_id, $variation_id = 0 ) {
        $wishlist_manager = WC_Zoho_B2B_Wishlist_Manager::get_instance();
        if ( ! $wishlist_manager->is_enabled() ) return '';

        $user_id = get_current_user_id();
        // If guest wishlists are off and user is not logged in, don't show button (or show login prompt)
        if ( ! $user_id && ! apply_filters( 'wczb2b_allow_guest_wishlist', false ) ) {
             return '<a href="' . esc_url(wc_get_page_permalink('myaccount')) . '" class="wczb2b-wishlist-button wczb2b-login-to-wishlist">' . esc_html__('Login to Add to Wishlist', 'wc-zoho-b2b') . '</a>';
        }

        $product = wc_get_product( $variation_id ? $variation_id : $product_id );
        if (!$product) return '';

        $is_in_wishlist = $wishlist_manager->is_in_wishlist( $user_id, $product_id, $variation_id );

        $button_text = $is_in_wishlist ? __( '✓ Added to Wishlist', 'wc-zoho-b2b' ) : __( '♡ Add to Wishlist', 'wc-zoho-b2b' );
        $button_action = $is_in_wishlist ? 'remove' : 'add';
        $button_class = 'wczb2b-wishlist-button button ' . ( $is_in_wishlist ? 'added' : '' );

        $html = sprintf(
            '<button type="button" class="%s" data-action="wczb2b_%s_to_wishlist" data-product-id="%d" data-variation-id="%d" aria-label="%s">%s</button>',
            esc_attr( $button_class ),
            esc_attr( $button_action ), // This will be part of the AJAX action name
            esc_attr( $product_id ),
            esc_attr( $variation_id ),
            esc_attr( $button_text ),
            esc_html( $button_text )
        );
        // Add a wrapper for easier JS targeting and loading states
        return '<div class="wczb2b-wishlist-button-wrapper">' . $html . '</div>';
    }
}
?>
