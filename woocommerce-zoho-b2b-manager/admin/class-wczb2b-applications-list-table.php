<?php
/**
 * Class for displaying B2B applications in a WP_List_Table.
 *
 * @link       https://example.com/woocommerce-zoho-b2b-manager
 * @since      1.0.0
 *
 * @package    WooCommerce_Zoho_B2B_Manager
 * @subpackage WooCommerce_Zoho_B2B_Manager/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Ensure WP_List_Table is available
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class WCZB2B_Applications_List_Table extends WP_List_Table {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct( array(
            'singular' => __( 'B2B Application', 'wc-zoho-b2b' ), // Singular name of the listed records
            'plural'   => __( 'B2B Applications', 'wc-zoho-b2b' ),// Plural name of the listed records
            'ajax'     => false, // Does this table support ajax?
        ) );
    }

    /**
     * Retrieve applications data from the database.
     *
     * @param int $per_page
     * @param int $page_number
     * @return array
     */
    public static function get_applications_data( $per_page = 20, $page_number = 1, $status_filter = '' ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wc_zoho_b2b_applications';
        $sql = "SELECT * FROM {$table_name}";

        $where_clauses = array();
        if ( ! empty( $status_filter ) && $status_filter !== 'all') {
            $where_clauses[] = $wpdb->prepare( "status = %s", $status_filter );
        }

        // Search functionality (example)
        if ( ! empty( $_REQUEST['s'] ) ) {
            $search_term = sanitize_text_field( wp_unslash($_REQUEST['s']) );
            $where_clauses[] = $wpdb->prepare( "(company_name LIKE %s OR email LIKE %s OR contact_person LIKE %s)", "%{$search_term}%", "%{$search_term}%", "%{$search_term}%" );
        }


        if ( ! empty( $where_clauses ) ) {
            $sql .= " WHERE " . implode( ' AND ', $where_clauses );
        }

        // Order by
        $orderby = isset( $_REQUEST['orderby'] ) ? sanitize_sql_orderby( $_REQUEST['orderby'] ) : 'applied_date';
        $order   = isset( $_REQUEST['order'] ) && in_array( strtoupper( $_REQUEST['order'] ), array( 'ASC', 'DESC' ) ) ? strtoupper( $_REQUEST['order'] ) : 'DESC';
        $sql .= " ORDER BY {$orderby} {$order}";

        $sql .= $wpdb->prepare( " LIMIT %d OFFSET %d", $per_page, ( $page_number - 1 ) * $per_page );

        $result = $wpdb->get_results( $sql, 'ARRAY_A' );
        return $result;
    }

    /**
     * Delete an application record.
     *
     * @param int $id application ID
     */
    public static function delete_application( $id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wc_zoho_b2b_applications';
        $wpdb->delete( $table_name, array( 'id' => $id ), array( '%d' ) );
    }

    /**
     * Returns the count of records in the database.
     *
     * @return null|string
     */
    public static function record_count( $status_filter = '' ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wc_zoho_b2b_applications';
        $sql = "SELECT COUNT(*) FROM {$table_name}";

        if ( ! empty( $status_filter ) && $status_filter !== 'all') {
            $sql .= $wpdb->prepare( " WHERE status = %s", $status_filter );
        }
        return $wpdb->get_var( $sql );
    }

    /** Text displayed when no application data is available */
    public function no_items() {
        esc_html_e( 'No B2B applications found.', 'wc-zoho-b2b' );
    }

    /**
     * Render a column when no column specific method exists.
     *
     * @param array $item
     * @param string $column_name
     * @return mixed
     */
    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'company_name':
            case 'email':
            case 'contact_person':
            case 'phone':
            case 'status':
            case 'applied_date':
            case 'processed_date':
                return esc_html( $item[ $column_name ] );
            default:
                return print_r( $item, true ); //Show the whole array for troubleshooting purposes
        }
    }

    /**
     *  Associative array of columns
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            'cb'             => '<input type="checkbox" />',
            'company_name'   => __( 'Company Name', 'wc-zoho-b2b' ),
            'email'          => __( 'Email', 'wc-zoho-b2b' ),
            'contact_person' => __( 'Contact Person', 'wc-zoho-b2b' ),
            'status'         => __( 'Status', 'wc-zoho-b2b' ),
            'applied_date'   => __( 'Applied Date', 'wc-zoho-b2b' ),
            'actions_col'    => __( 'Actions', 'wc-zoho-b2b' ), // Custom column for actions
        );
        return $columns;
    }

    /**
     * Columns to make sortable.
     *
     * @return array
     */
    public function get_sortable_columns() {
        $sortable_columns = array(
            'company_name' => array( 'company_name', true ),
            'email'        => array( 'email', false ),
            'status'       => array( 'status', false ),
            'applied_date' => array( 'applied_date', false ),
        );
        return $sortable_columns;
    }

    /**
     * Render the checkbox column.
     */
    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="bulk-ids[]" value="%s" />', $item['id']
        );
    }

    /**
     * Column for 'Company Name' with actions.
     */
    function column_company_name( $item ) {
        $title = '<strong>' . esc_html( $item['company_name'] ) . '</strong>';
        // Nonce for view/edit might be needed if we have a separate edit screen
        // $view_nonce = wp_create_nonce( 'wczb2b_view_application_' . $item['id'] );
        // $actions['view'] = sprintf( '<a href="?page=%s&action=%s&application_id=%s&_wpnonce=%s">View Details</a>', esc_attr( $_REQUEST['page'] ), 'view_application', absint( $item['id'] ), $view_nonce );

        // For now, actions will be in a dedicated 'Actions' column.
        // If you want actions under company name:
        // $actions = array(); // define actions here
        // return $title . $this->row_actions( $actions );
        return $title;
    }

    /**
     * Column for 'Status'.
     */
    function column_status( $item ) {
        $status_label = ucfirst( $item['status'] );
        $status_class = sanitize_html_class( 'status-' . $item['status'] );
        return sprintf( '<span class="wczb2b-status %s">%s</span>', $status_class, esc_html( $status_label ) );
    }

    /**
     * Custom column for actions.
     */
    function column_actions_col($item) {
        $actions = array();
        $page_slug = esc_attr( $_REQUEST['page'] ); // Should be 'wczb2b-applications'

        if ($item['status'] === 'pending') {
            $approve_nonce = wp_create_nonce('wczb2b_approve_app_' . $item['id']);
            $actions['approve'] = sprintf(
                '<a href="?action=%s&application_id=%s&_wpnonce=%s&page=%s" class="button button-primary">%s</a>',
                'wczb2b_approve_application', // This is the admin_action hook
                absint($item['id']),
                $approve_nonce,
                $page_slug, // Keep on the same page
                __('Approve', 'wc-zoho-b2b')
            );
            $reject_nonce = wp_create_nonce('wczb2b_reject_app_' . $item['id']);
            $actions['reject'] = sprintf(
                '<a href="?action=%s&application_id=%s&_wpnonce=%s&page=%s" class="button button-secondary">%s</a>',
                'wczb2b_reject_application', // admin_action hook
                absint($item['id']),
                $reject_nonce,
                $page_slug,
                __('Reject', 'wc-zoho-b2b')
            );
        } elseif ($item['status'] === 'approved' || $item['status'] === 'rejected') {
            // Option to revert or view details
            // $actions['view_details'] = sprintf('<a href="#">%s</a>', __('View Details', 'wc-zoho-b2b'));
        }

        // Add delete action (use with caution)
        // $delete_nonce = wp_create_nonce('wczb2b_delete_app_' . $item['id']);
        // $actions['delete'] = sprintf(
        //     '<a href="?action=%s&application_id=%s&_wpnonce=%s&page=%s" class="button button-link delete" onclick="return confirm(\'%s\');">%s</a>',
        //     'wczb2b_delete_application', // admin_action hook
        //     absint($item['id']),
        //     $delete_nonce,
        //     $page_slug,
        //     esc_attr__('Are you sure you want to delete this application? This cannot be undone.', 'wc-zoho-b2b'),
        //     __('Delete', 'wc-zoho-b2b')
        // );

        return $this->row_actions($actions, true); // true for always_visible
    }


    /**
     *  Define bulk actions.
     *
     * @return array
     */
    public function get_bulk_actions() {
        $actions = array(
            'bulk-approve' => __('Approve', 'wc-zoho-b2b'),
            'bulk-reject'  => __('Reject', 'wc-zoho-b2b'),
            // 'bulk-delete'  => __('Delete', 'wc-zoho-b2b'), // Use with caution
        );
        return $actions;
    }

    /**
     * Handles data query and filter, sorting, and pagination.
     */
    public function prepare_items() {
        $this->_column_headers = $this->get_column_info();

        /** Process bulk action */
        $this->process_bulk_action();

        $per_page     = $this->get_items_per_page( 'applications_per_page', 20 );
        $current_page = $this->get_pagenum();
        $status_filter = isset($_REQUEST['status_filter']) ? sanitize_text_field($_REQUEST['status_filter']) : '';

        $total_items  = self::record_count($status_filter);

        $this->set_pagination_args( array(
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page'    => $per_page, //WE have to determine how many items to show on a page
        ) );

        $this->items = self::get_applications_data( $per_page, $current_page, $status_filter );
    }

    /**
     * Display the status filter dropdown.
     */
    protected function extra_tablenav( $which ) {
        if ( 'top' === $which ) {
            $status_filter = isset( $_REQUEST['status_filter'] ) ? sanitize_text_field( $_REQUEST['status_filter'] ) : '';
            ?>
            <div class="alignleft actions">
                <label for="status_filter" class="screen-reader-text"><?php esc_html_e( 'Filter by status', 'wc-zoho-b2b' ); ?></label>
                <select name="status_filter" id="status_filter">
                    <option value="all" <?php selected( $status_filter, 'all' ); ?>><?php esc_html_e( 'All Statuses', 'wc-zoho-b2b' ); ?></option>
                    <option value="pending" <?php selected( $status_filter, 'pending' ); ?>><?php esc_html_e( 'Pending', 'wc-zoho-b2b' ); ?></option>
                    <option value="approved" <?php selected( $status_filter, 'approved' ); ?>><?php esc_html_e( 'Approved', 'wc-zoho-b2b' ); ?></option>
                    <option value="rejected" <?php selected( $status_filter, 'rejected' ); ?>><?php esc_html_e( 'Rejected', 'wc-zoho-b2b' ); ?></option>
                </select>
                <?php submit_button( __( 'Filter' ), 'secondary', 'do_filter_status', false ); ?>
            </div>
            <?php
        }
    }

    /**
     * Process bulk actions.
     */
    public function process_bulk_action() {
        $action = $this->current_action();
        $ids = isset( $_REQUEST['bulk-ids'] ) ? array_map( 'absint', (array) $_REQUEST['bulk-ids'] ) : array();

        if ( empty( $action ) || empty( $ids ) ) {
            return;
        }

        // Check nonce for bulk actions
        // check_admin_referer( 'bulk-' . $this->_args['plural'] ); // Default nonce check by WP_List_Table

        $user_manager = WC_Zoho_B2B_User_Manager::get_instance();

        if ( 'bulk-approve' === $action ) {
            // check_admin_referer( 'wczb2b_bulk_action_nonce', 'wczb2b_nonce_field_name' ); // Custom nonce if needed
            foreach ( $ids as $id ) {
                // $user_manager->process_approve_application_by_id( $id ); // Create this method in User_Manager
                 wczb2b_log( "Bulk approving application ID: $id (logic to be implemented in User_Manager)", 'info');
            }
            // Add admin notice for bulk approval
        }

        if ( 'bulk-reject' === $action ) {
            foreach ( $ids as $id ) {
                // $user_manager->process_reject_application_by_id( $id ); // Create this method in User_Manager
                wczb2b_log( "Bulk rejecting application ID: $id (logic to be implemented in User_Manager)", 'info');
            }
            // Add admin notice for bulk rejection
        }

        // if ( 'bulk-delete' === $action ) {
        //     foreach ( $ids as $id ) {
        //         self::delete_application( $id );
        //     }
        //     // Add admin notice for bulk delete
        // }

        // Redirect after processing to clear URL params (optional, depends on how notices are handled)
        // wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'action', 'action2', 'bulk-ids' ), wp_get_referer() ) );
        // exit;
    }
}
?>
