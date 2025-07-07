<?php
/**
 * Manages B2B specific pricing, discounts, and calculation methods.
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

class WC_Zoho_B2B_Pricing_Manager {

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
        // Hooks for modifying product prices based on user role or other B2B criteria
        // The priority 100 is to ensure it runs after most other price modifications.
        add_filter( 'woocommerce_product_get_price', array( $this, 'get_b2b_price' ), 100, 2 );
        add_filter( 'woocommerce_product_get_regular_price', array( $this, 'get_b2b_regular_price' ), 100, 2 );
        add_filter( 'woocommerce_product_get_sale_price', array( $this, 'get_b2b_sale_price' ), 100, 2 );

        // For variations
        add_filter( 'woocommerce_product_variation_get_price', array( $this, 'get_b2b_price' ), 100, 2 );
        add_filter( 'woocommerce_product_variation_get_regular_price', array( $this, 'get_b2b_regular_price' ), 100, 2 );
        add_filter( 'woocommerce_product_variation_get_sale_price', array( $this, 'get_b2b_sale_price' ), 100, 2 );

        // To make sure the price HTML reflects the B2B price (e.g., for sale price display)
        add_filter( 'woocommerce_get_price_html', array( $this, 'b2b_price_html' ), 100, 2 );

        // Hook for cart item price calculation (important for totals)
        add_action( 'woocommerce_before_calculate_totals', array( $this, 'apply_b2b_price_to_cart_items' ), 100 );
    }

    /**
     * Get B2B specific price for a product.
     * This is the main filter for the 'active' price.
     *
     * @param float      $price   Original price.
     * @param WC_Product $product Product object.
     * @return float Modified B2B price.
     */
    public function get_b2b_price( $price, $product ) {
        if ( $this->should_apply_b2b_pricing( $product ) ) {
            $b2b_price = $this->calculate_b2b_price_for_product( $price, $product );
            // Ensure that if a B2B price is applied, it's not higher than the original price
            // unless specific B2B fixed pricing is implemented and intended.
            // For discounts, this is naturally handled. For fixed prices, it needs consideration.
            return $b2b_price;
        }
        return $price;
    }

    /**
     * Get B2B specific regular price.
     */
    public function get_b2b_regular_price( $regular_price, $product ) {
         if ( $this->should_apply_b2b_pricing( $product ) ) {
            // If B2B pricing is a discount, it should apply to the 'active' price.
            // If it's a fixed B2B price, that fixed price IS the regular B2B price.
            // For percentage discounts, we typically show the discount off the regular price
            // if there's no sale, or off the sale price if there is one.
            // The get_b2b_price filter handles the active price.
            // This filter is for what WooCommerce considers the 'regular' price.
            // If we have a B2B fixed price, that's the B2B regular price.
            // If it's a discount, the B2B regular price is the original regular price discounted.
             $pricing_method = get_option('wc_zoho_b2b_pricing_method', 'percentage_discount');
             if ($pricing_method === 'fixed_price') {
                // $fixed_b2b_price = get_post_meta( $product->get_id(), '_wczb2b_fixed_price', true );
                // if ( $fixed_b2b_price !== '' ) return $fixed_b2b_price;
                // For now, assume fixed price isn't implemented via this filter yet.
             } elseif ($pricing_method === 'percentage_discount') {
                 $discount_percentage = $this->get_user_role_discount_percentage();
                 if ($discount_percentage > 0) {
                     return $regular_price * (1 - ($discount_percentage / 100));
                 }
             }
        }
        return $regular_price;
    }

    /**
     * Get B2B specific sale price.
     */
    public function get_b2b_sale_price( $sale_price, $product ) {
        if ( $this->should_apply_b2b_pricing( $product ) ) {
            // If there's a B2B discount, and the product is on sale,
            // the B2B price is typically the original sale price further discounted,
            // or a fixed B2B price if that's lower.
            // The get_b2b_price method already determines the final active price.
            // This sale_price filter is tricky. If B2B price is a discount,
            // it's applied to the already determined sale price (by get_b2b_price).
            // If the B2B price makes the concept of a "sale" irrelevant (e.g. fixed B2B price always shown),
            // then this could return an empty string, or the B2B price if it's lower than B2B regular.
            $b2b_active_price = $this->calculate_b2b_price_for_product( $product->get_price('edit'), $product ); // Get potentially discounted price
            $b2b_regular_price = $this->calculate_b2b_price_for_product( $product->get_regular_price('edit'), $product, true); // Discount regular price

            if ($b2b_active_price < $b2b_regular_price) {
                return $b2b_active_price; // This is effectively the B2B sale price
            }
            // If B2B active price is same as B2B regular (e.g. no sale, or fixed B2B price), then no specific "B2B sale price"
            return '';
        }
        return $sale_price;
    }

    /**
     * Modify the price HTML for B2B customers.
     */
    public function b2b_price_html( $price_html, $product ) {
        if ( $this->should_apply_b2b_pricing( $product ) && !is_admin() ) { // Don't alter in admin
            // WC re-calculates price HTML based on the filtered get_price, get_regular_price, get_sale_price.
            // This function might be redundant if the other filters are set up correctly,
            // but can be used for custom HTML formatting if needed, e.g., showing "Your Price:".
            // For now, let WooCommerce rebuild it based on our filtered prices.
            // Example: return '<span class="wczb2b-b2b-price-label">' . __('Your Price:', 'wc-zoho-b2b') . '</span> ' . wc_price($product->get_price());
        }
        return $price_html;
    }

    /**
     * Apply B2B pricing to items in the cart.
     */
    public function apply_b2b_price_to_cart_items( $cart ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;

        foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
            $product = $cart_item['data'];
            if ( $this->should_apply_b2b_pricing( $product ) ) {
                // Get the B2B price using the same logic as single product pages
                // The get_b2b_price filter should already be applied when WC gets product price for cart.
                // However, to be absolutely sure or if there's complex logic not covered by simple price filters:
                $original_price = $product->get_price('edit'); // Get price without our filters for calculation base
                $b2b_price = $this->calculate_b2b_price_for_product($original_price, $product);

                if ($b2b_price !== false && $b2b_price !== $original_price ) {
                     $cart_item['data']->set_price( $b2b_price );
                }
            }
        }
    }

    /**
     * Helper function to determine if B2B pricing should be applied.
     */
    private function should_apply_b2b_pricing( $product ) {
        if ( ! $product ) return false;

        // Don't apply B2B pricing if prices are hidden for guests and user is not logged in
        // (Though wczb2b_is_b2b_customer also checks for login)
        $show_prices_logged_out = get_option('wc_zoho_b2b_show_prices_logged_out', 'no');
        if ($show_prices_logged_out === 'no' && !is_user_logged_in()) {
            return false;
        }

        return wczb2b_is_b2b_customer(); // Use global helper
    }

    /**
     * Calculates the B2B price for a product based on configured method.
     * @param float $current_price The current price of the product (could be sale or regular).
     * @param WC_Product $product
     * @param bool $force_regular If true, calculates based on regular price, not sale price.
     * @return float|bool B2B price, or original price if no B2B rule applies.
     */
    private function calculate_b2b_price_for_product( $current_price, $product, $force_regular = false ) {
        $pricing_method = get_option('wc_zoho_b2b_pricing_method', 'percentage_discount');
        $final_price = $current_price;

        if ($pricing_method === 'fixed_price') {
            // TODO: Implement fixed price per product (requires product meta)
            // $fixed_b2b_price = get_post_meta( $product->get_id(), '_wczb2b_fixed_price', true );
            // if ( $fixed_b2b_price !== '' && is_numeric($fixed_b2b_price) ) {
            //     $final_price = (float) $fixed_b2b_price;
            // }
        } elseif ($pricing_method === 'percentage_discount') {
            $discount_percentage = $this->get_user_role_discount_percentage();
            if ($discount_percentage > 0) {
                $price_to_discount = $current_price;
                if($force_regular){ // If we specifically want to discount the regular price
                    $price_to_discount = $product->get_regular_price('edit');
                } elseif ($product->is_on_sale('edit')) { // If product is on sale, discount the sale price
                    $price_to_discount = $product->get_sale_price('edit');
                } else { // Otherwise, discount the regular price (which $current_price would be)
                     $price_to_discount = $product->get_regular_price('edit');
                }
                 if($price_to_discount === '' || $price_to_discount === null) $price_to_discount = $current_price; // Fallback

                $final_price = (float)$price_to_discount * (1 - ($discount_percentage / 100));
            }
        }

        // Allow other plugins to modify this B2B price
        return apply_filters( 'wczb2b_calculated_b2b_price', $final_price, $current_price, $product, $pricing_method );
    }

    /**
     * Get the highest applicable discount percentage for the current user's roles.
     * @return float Discount percentage.
     */
    private function get_user_role_discount_percentage() {
        if ( ! is_user_logged_in() ) return 0;

        $user = wp_get_current_user();
        $user_roles = (array) $user->roles;
        $role_discounts = get_option('wc_zoho_b2b_role_discounts', array());
        $applicable_discount = 0;

        foreach ( $user_roles as $role ) {
            if ( isset( $role_discounts[ $role ] ) && is_numeric( $role_discounts[ $role ] ) ) {
                // Use the highest discount if user has multiple roles with discounts
                if ( (float) $role_discounts[ $role ] > $applicable_discount ) {
                    $applicable_discount = (float) $role_discounts[ $role ];
                }
            }
        }
        return $applicable_discount;
    }
}
?>
