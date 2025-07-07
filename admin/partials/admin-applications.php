<?php
/**
 * Provides the view for managing B2B applications.
 * This might be part of a larger user management screen or a dedicated page.
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

// The $this->applications_list_table_instance variable should be set by
// WC_Zoho_B2B_Admin::display_applications_page() which calls this partial.
// We make it global here to access it if display_applications_page sets it as such,
// or directly access it if $this is the WC_Zoho_B2B_Admin instance.
// A cleaner way is to pass it as an argument or ensure $this is the correct context.

// Assuming $this is an instance of WC_Zoho_B2B_Admin from where display_applications_page was called
// and $this->applications_list_table_instance was set.
// If not, this partial needs to be included differently or the variable passed.
// For simplicity, let's assume it's available through a known global or passed implicitly.

$list_table_instance = null;
if ( class_exists('WC_Zoho_B2B_Admin') ) {
    $admin_instance = WC_Zoho_B2B_Admin::get_instance();
    if ( isset($admin_instance->applications_list_table_instance) ) {
        $list_table_instance = $admin_instance->applications_list_table_instance;
    }
}

// Display messages from approve/reject actions
if (isset($_GET['message'])) {
    $message_type = sanitize_text_field($_GET['message']);
    $app_id = isset($_GET['app_id']) ? absint($_GET['app_id']) : 0;
    if ($message_type === 'approved' && $app_id) {
        add_settings_error('wczb2b_applications_notices', 'app_approved', sprintf(__('Application #%d approved successfully.', 'wc-zoho-b2b'), $app_id), 'updated');
    } elseif ($message_type === 'rejected' && $app_id) {
        add_settings_error('wczb2b_applications_notices', 'app_rejected', sprintf(__('Application #%d rejected.', 'wc-zoho-b2b'), $app_id), 'error');
    } elseif ($message_type === 'error') {
        add_settings_error('wczb2b_applications_notices', 'app_error', __('An error occurred processing the application.', 'wc-zoho-b2b'), 'error');
    }
}


?>
<div class="wrap wczb2b-applications-wrap">
    <h1><?php esc_html_e( 'B2B Applications', 'wc-zoho-b2b' ); ?></h1>

    <?php settings_errors('wczb2b_applications_notices'); ?>

    <p><?php esc_html_e( 'Review and manage pending B2B applications. You can approve or reject applications here.', 'wc-zoho-b2b' ); ?></p>

    <?php /* Search form is part of the list table display method usually */ ?>
    <form method="get">
        <?php /* WordPress adds the page slug automatically with GET forms on admin pages */ ?>
        <input type="hidden" name="page" value="<?php echo esc_attr(isset($_REQUEST['page']) ? sanitize_text_field(wp_unslash($_REQUEST['page'])) : 'wczb2b-applications'); ?>" />
        <?php
        if ( $list_table_instance instanceof WCZB2B_Applications_List_Table ) {
            $list_table_instance->prepare_items(); // Prepare items for display (fetches data, pagination, etc.)
            $list_table_instance->views();         // Display status filters (e.g., All, Pending, Approved)
            $list_table_instance->search_box( __( 'Search Applications', 'wc-zoho-b2b' ), 'wczb2b-application-search' );
            $list_table_instance->display();       // Render the table
        } else {
            // This message indicates that prepare_applications_list_table was not called or failed.
             echo '<div class="error"><p>' . esc_html__( 'Error: Applications list table could not be loaded. Ensure the WCZB2B_Applications_List_Table class is available and instantiated correctly.', 'wc-zoho-b2b' ) . '</p></div>';
             if (!class_exists('WCZB2B_Applications_List_Table')) {
                echo '<p>' . esc_html__('Debug: WCZB2B_Applications_List_Table class not found.', 'wc-zoho-b2b') . '</p>';
             }
        }
        ?>
    </form>
</div>
