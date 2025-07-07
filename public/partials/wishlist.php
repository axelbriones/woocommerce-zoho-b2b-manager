<?php
/**
 * Provides the view for the user's wishlist.
 * This template can be loaded via a shortcode.
 *
 * Passed variables:
 * @var array $wishlist_items Array of objects/arrays, each representing a wishlist item.
 *                            Expected properties/keys: product_id, variation_id, date_added.
 *
 * @link       https://example.com/woocommerce-zoho-b2b-manager
 * @since      1.0.0
 *
 * @package    WooCommerce_Zoho_B2B_Manager
 * @subpackage WooCommerce_Zoho_B2B_Manager/public/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$wishlist_manager = WC_Zoho_B2B_Wishlist_Manager::get_instance();
$frontend_manager = WC_Zoho_B2B_Frontend::get_instance(); // To get button HTML

?>
<div class="wczb2b-wishlist-wrapper woocommerce">
    <h2><?php esc_html_e( 'My Wishlist', 'wc-zoho-b2b' ); ?></h2>

    <?php if ( ! empty( $wishlist_items ) ) : ?>
        <form class="wczb2b-wishlist-form" method="post">
            <table class="shop_table shop_table_responsive cart wishlist_table" cellspacing="0">
                <thead>
                    <tr>
                        <th class="product-remove" scope="col">&nbsp;</th>
                        <th class="product-thumbnail" scope="col">&nbsp;</th>
                        <th class="product-name" scope="col"><?php esc_html_e( 'Product', 'wc-zoho-b2b' ); ?></th>
                        <th class="product-price" scope="col"><?php esc_html_e( 'Price', 'wc-zoho-b2b' ); ?></th>
                        <th class="product-stock-status" scope="col"><?php esc_html_e( 'Stock status', 'wc-zoho-b2b' ); ?></th>
                        <th class="product-add-to-cart" scope="col">&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $wishlist_items as $item ) : ?>
                        <?php
                        $product_id   = $item->product_id;
                        $variation_id = $item->variation_id;
                        $_product     = wc_get_product( $variation_id ? $variation_id : $product_id );

                        if ( ! $_product || ! $_product->exists() ) {
                            // Product might have been deleted, good to have a way to clear such items.
                            // For now, just skip. Consider adding a "remove invalid item" feature.
                            wczb2b_log("Wishlist: Product ID {$product_id} (Variation ID: {$variation_id}) not found or does not exist. Skipping.", 'warning');
                            continue;
                        }
                        $product_permalink = $_product->get_permalink();
                        ?>
                        <tr class="woocommerce-wishlist-item <?php echo esc_attr( apply_filters( 'wczb2b_wishlist_item_class', 'wishlist_item', $_product, $item ) ); ?>">
                            <td class="product-remove">
                                <?php
                                // Output the remove button using the same logic as the single product page for consistency in AJAX handling
                                echo $frontend_manager->get_wishlist_button_html($product_id, $variation_id);
                                ?>
                            </td>
                            <td class="product-thumbnail">
                                <a href="<?php echo esc_url( $product_permalink ); ?>">
                                    <?php echo wp_kses_post( $_product->get_image('woocommerce_thumbnail') ); ?>
                                </a>
                            </td>
                            <td class="product-name" data-title="<?php esc_attr_e( 'Product', 'wc-zoho-b2b' ); ?>">
                                <a href="<?php echo esc_url( $product_permalink ); ?>"><?php echo wp_kses_post( $_product->get_name() ); ?></a>
                                <?php
                                // Display variation details if it's a variation
                                if ($_product->is_type('variation')) {
                                    echo wc_get_formatted_variation($_product, true);
                                }
                                ?>
                            </td>
                            <td class="product-price" data-title="<?php esc_attr_e( 'Price', 'wc-zoho-b2b' ); ?>">
                                <?php echo wp_kses_post( $_product->get_price_html() ); ?>
                            </td>
                            <td class="product-stock-status" data-title="<?php esc_attr_e( 'Stock status', 'wc-zoho-b2b' ); ?>">
                                <?php
                                $availability = $_product->get_availability();
                                $stock_status = isset( $availability['class'] ) ? $availability['class'] : '';
                                $availability_html = empty( $availability['availability'] ) ? '' : '<p class="stock ' . esc_attr( $stock_status ) . '">' . wp_kses_post( $availability['availability'] ) . '</p>';
                                echo apply_filters( 'woocommerce_stock_html', $availability_html, $availability['availability'], $_product );
                                ?>
                            </td>
                            <td class="product-add-to-cart">
                                <?php
                                // Display "Add to cart" button
                                if ( $_product->is_in_stock() && $_product->is_purchasable() ) {
                                    woocommerce_template_loop_add_to_cart( array(
                                        'quantity' => 1, // Default quantity
                                        'class'    => implode( ' ', array_filter( array(
                                            'button',
                                            'product_type_' . $_product->get_type(),
                                            $_product->is_purchasable() && $_product->is_in_stock() ? 'add_to_cart_button' : '',
                                            $_product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',
                                        ) ) )
                                    ) );
                                } else {
                                    echo '<span class="outofstock">' . esc_html__( 'Out of stock', 'wc-zoho-b2b') . '</span>';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php // wp_nonce_field( 'wczb2b_update_wishlist_action', 'wczb2b_wishlist_nonce' ); // For potential bulk actions like "Add all to cart" ?>
        </form>
    <?php else : ?>
        <div class="woocommerce-info woocommerce-info--empty-wishlist">
            <?php esc_html_e( 'Your wishlist is currently empty.', 'wc-zoho-b2b' ); ?>
            <a class="button wc-backward" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
                <?php esc_html_e( 'Return to shop', 'wc-zoho-b2b' ); ?>
            </a>
        </div>
    <?php endif; ?>
</div>
