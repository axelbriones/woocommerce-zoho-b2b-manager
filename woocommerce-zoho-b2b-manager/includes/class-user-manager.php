<?php
/**
 * Manages B2B user registration, applications, and roles.
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

class WC_Zoho_B2B_User_Manager {

    private static $instance;
    private $db_applications_table_name;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     * Sets up hooks for user management related actions.
     */
    public function __construct() {
        global $wpdb;
        $this->db_applications_table_name = $wpdb->prefix . 'wc_zoho_b2b_applications';

        // Hooks for handling B2B application form submission (frontend)
        // The actual shortcode rendering is in WC_Zoho_B2B_Frontend,
        // but this class handles the POST request.
        add_action( 'wp_loaded', array( $this, 'maybe_handle_application_form_submission' ) ); // wp_loaded is a good hook for POST submissions before headers are sent.

        // Hooks for admin actions like approving/rejecting applications
        // These are typically triggered by links in a WP_List_Table for applications.
        add_action( 'admin_action_wczb2b_approve_application', array( $this, 'handle_admin_approve_application' ) );
        add_action( 'admin_action_wczb2b_reject_application', array( $this, 'handle_admin_reject_application' ) );
        // Hooks for admin actions like approving/rejecting applications
        // These are triggered by links in a WP_List_Table for applications.
        add_action( 'admin_action_wczb2b_approve_application', array( $this, 'process_admin_approve_application_action' ) );
        add_action( 'admin_action_wczb2b_reject_application', array( $this, 'process_admin_reject_application_action' ) );

        // Add B2B fields to user profile page (admin and frontend)
        // add_action( 'show_user_profile', array( $this, 'display_b2b_user_profile_fields' ) ); // Frontend My Account
        // add_action( 'edit_user_profile', array( $this, 'display_b2b_user_profile_fields' ) ); // Admin User Edit
        // add_action( 'personal_options_update', array( $this, 'save_b2b_user_profile_fields' ) );
        // add_action( 'edit_user_profile_update', array( $this, 'save_b2b_user_profile_fields' ) );
    }

    /**
     * Checks if an application form has been submitted and processes it.
     * Hooked to 'wp_loaded'.
     *
     * @since 1.0.0
     */
    public function maybe_handle_application_form_submission() {
        if ( ! isset( $_POST['wczb2b_application_nonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wczb2b_application_nonce_field'] ) ), 'wczb2b_submit_application_action' ) ) {
            return; // Nonce check failed or form not submitted
        }

        // Basic spam check (honeypot)
        if ( ! empty( $_POST['wczb2b_hp_email'] ) ) {
            wczb2b_log( 'B2B application submission failed honeypot check.', 'warning' );
            // You might want to redirect with an error or silently fail.
            // For now, just return.
            return;
        }

        wczb2b_log( 'B2B Application form submitted. Processing...', 'info' );

        $required_fields = array(
            'wczb2b_company_name' => __( 'Company Name', 'wc-zoho-b2b' ),
            'wczb2b_email'        => __( 'Email Address', 'wc-zoho-b2b' ),
            // Add other required fields here, e.g., contact person
        );
        $errors = new WP_Error();

        $form_data = array();
        foreach ($required_fields as $key => $label) {
            if (empty($_POST[$key])) {
                $errors->add('field_required', sprintf(esc_html__('%s is required.', 'wc-zoho-b2b'), $label));
            }
        }

        $form_data['company_name']   = isset( $_POST['wczb2b_company_name'] ) ? sanitize_text_field( wp_unslash( $_POST['wczb2b_company_name'] ) ) : '';
        $form_data['email']          = isset( $_POST['wczb2b_email'] ) ? sanitize_email( wp_unslash( $_POST['wczb2b_email'] ) ) : '';
        $form_data['tax_id']         = isset( $_POST['wczb2b_tax_id'] ) ? sanitize_text_field( wp_unslash( $_POST['wczb2b_tax_id'] ) ) : '';
        $form_data['business_type']  = isset( $_POST['wczb2b_business_type'] ) ? sanitize_text_field( wp_unslash( $_POST['wczb2b_business_type'] ) ) : '';
        $form_data['contact_person'] = isset( $_POST['wczb2b_contact_person'] ) ? sanitize_text_field( wp_unslash( $_POST['wczb2b_contact_person'] ) ) : '';
        $form_data['phone']          = isset( $_POST['wczb2b_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['wczb2b_phone'] ) ) : ''; // Consider more specific phone sanitization/validation
        $form_data['address']        = isset( $_POST['wczb2b_address'] ) ? sanitize_textarea_field( wp_unslash( $_POST['wczb2b_address'] ) ) : '';
        $form_data['notes']          = isset( $_POST['wczb2b_applicant_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['wczb2b_applicant_notes'] ) ) : ''; // Applicant's notes

        if ( ! is_email( $form_data['email'] ) ) {
             $errors->add('invalid_email', __( 'Please enter a valid email address.', 'wc-zoho-b2b' ));
        }

        // Check if email already exists as a user or in applications
        $user_id_exists = email_exists( $form_data['email'] );
        if ( $user_id_exists ) {
            // If user is logged in and it's their own email, allow application if they are not already B2B.
            // If user is guest, or logged in but email belongs to another user, it's an error.
            if ( !is_user_logged_in() || (is_user_logged_in() && get_current_user_id() != $user_id_exists ) ) {
                $errors->add('email_exists', __( 'This email address is already registered. If this is your account, please log in to apply.', 'wc-zoho-b2b' ));
            } else if (is_user_logged_in() && get_current_user_id() == $user_id_exists && wczb2b_is_b2b_customer($user_id_exists) ){
                 $errors->add('already_b2b', __( 'Your account is already approved as a B2B customer.', 'wc-zoho-b2b' ));
            }
        }

        // Check if an application with this email is already pending or approved
        global $wpdb;
        $existing_application = $wpdb->get_row( $wpdb->prepare(
            "SELECT status FROM {$this->db_applications_table_name} WHERE email = %s AND (status = 'pending' OR status = 'approved')",
            $form_data['email']
        ));

        if ($existing_application) {
            if ($existing_application->status === 'pending') {
                $errors->add('application_pending', __('An application with this email address is already pending review.', 'wc-zoho-b2b'));
            } elseif ($existing_application->status === 'approved') {
                 // This case should ideally be caught by email_exists and wczb2b_is_b2b_customer if user was created
                $errors->add('application_approved', __('An application with this email address has already been approved.', 'wc-zoho-b2b'));
            }
        }


        if ( $errors->has_errors() ) {
            $error_messages = $errors->get_error_messages();
            set_transient('wczb2b_application_message', implode('<br>', $error_messages), MINUTE_IN_SECONDS);
            set_transient('wczb2b_application_message_type', 'error', MINUTE_IN_SECONDS);
            // To re-populate form, we might need to store $_POST data in session or pass it back if not redirecting.
            // For now, just show error. User will have to re-fill.
            // Or, redirect back to the form page.
            $redirect_url = wp_get_referer() ? wp_get_referer() : get_permalink();
            wp_safe_redirect( add_query_arg( 'application_error', 'validation', $redirect_url ) );
            exit;
        }

        // All checks passed, proceed to insert application
        // global $wpdb; // Already global from above
        $insert_data = array(
            'company_name'   => $form_data['company_name'],
            'email'          => $form_data['email'],
            'tax_id'         => $form_data['tax_id'],
            'business_type'  => $form_data['business_type'],
            'contact_person' => $form_data['contact_person'],
            'phone'          => $form_data['phone'],
            'address'        => $form_data['address'],
            'notes'          => $form_data['notes'],
            'status'         => 'pending',
            'applied_date'   => current_time( 'mysql', true ),
        );

        if ( is_user_logged_in() ) {
            $insert_data['user_id'] = get_current_user_id();
        }

        $result = $wpdb->insert( $this->db_applications_table_name, $insert_data );

        if ( $result === false ) {
            wczb2b_log( 'Failed to insert B2B application into database. DB Error: ' . $wpdb->last_error, 'error' );
            set_transient('wczb2b_application_message', __('An internal error occurred while submitting your application. Please try again later.', 'wc-zoho-b2b'), MINUTE_IN_SECONDS);
            set_transient('wczb2b_application_message_type', 'error', MINUTE_IN_SECONDS);
            $redirect_url = wp_get_referer() ? wp_get_referer() : get_permalink();
            wp_safe_redirect( add_query_arg( 'application_error', 'db_error', $redirect_url ) );
            exit;
        }

        $application_id = $wpdb->insert_id;
        wczb2b_log( "B2B Application #{$application_id} submitted successfully for email: " . $form_data['email'], 'info' );

        do_action( 'wczb2b_after_application_submitted', $application_id, $form_data );

        // TODO: Send notification emails (admin and applicant)

        set_transient('wczb2b_application_message', __('Thank you for your application! We will review it shortly.', 'wc-zoho-b2b'), MINUTE_IN_SECONDS);
        set_transient('wczb2b_application_message_type', 'success', MINUTE_IN_SECONDS);

        $redirect_url = wp_get_referer() ? wp_get_referer() : get_permalink();
        // Remove potential error query args before adding success one
        $redirect_url = remove_query_arg( 'application_error', $redirect_url );
        wp_safe_redirect( add_query_arg( 'application_submitted', 'success', $redirect_url ) );
        exit;
    }

    /**
     * Checks if an application form has been submitted and processes it.
     * Hooked to 'wp_loaded'.
     *
     * @since 1.0.0
     */
    public function maybe_handle_application_form_submission() {
        if ( ! isset( $_POST['wczb2b_application_nonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wczb2b_application_nonce_field'] ) ), 'wczb2b_submit_application_action' ) ) {
            return; // Nonce check failed or form not submitted
        }

        // Basic spam check (honeypot)
        if ( ! empty( $_POST['wczb2b_hp_email'] ) ) {
            wczb2b_log( 'B2B application submission failed honeypot check.', 'warning' );
            // You might want to redirect with an error or silently fail.
            // For now, just return.
            return;
        }

        wczb2b_log( 'B2B Application form submitted. Processing...', 'info' );

        $required_fields = array(
            'wczb2b_company_name' => __( 'Company Name', 'wc-zoho-b2b' ),
            'wczb2b_email'        => __( 'Email Address', 'wc-zoho-b2b' ),
            // Add other required fields here, e.g., contact person
        );
        $errors = new WP_Error();

        $form_data = array();
        foreach ($required_fields as $key => $label) {
            if (empty($_POST[$key])) {
                $errors->add('field_required', sprintf(esc_html__('%s is required.', 'wc-zoho-b2b'), $label));
            }
        }

        $form_data['company_name']   = isset( $_POST['wczb2b_company_name'] ) ? sanitize_text_field( wp_unslash( $_POST['wczb2b_company_name'] ) ) : '';
        $form_data['email']          = isset( $_POST['wczb2b_email'] ) ? sanitize_email( wp_unslash( $_POST['wczb2b_email'] ) ) : '';
        $form_data['tax_id']         = isset( $_POST['wczb2b_tax_id'] ) ? sanitize_text_field( wp_unslash( $_POST['wczb2b_tax_id'] ) ) : '';
        $form_data['business_type']  = isset( $_POST['wczb2b_business_type'] ) ? sanitize_text_field( wp_unslash( $_POST['wczb2b_business_type'] ) ) : '';
        $form_data['contact_person'] = isset( $_POST['wczb2b_contact_person'] ) ? sanitize_text_field( wp_unslash( $_POST['wczb2b_contact_person'] ) ) : '';
        $form_data['phone']          = isset( $_POST['wczb2b_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['wczb2b_phone'] ) ) : ''; // Consider more specific phone sanitization/validation
        $form_data['address']        = isset( $_POST['wczb2b_address'] ) ? sanitize_textarea_field( wp_unslash( $_POST['wczb2b_address'] ) ) : '';
        $form_data['notes']          = isset( $_POST['wczb2b_applicant_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['wczb2b_applicant_notes'] ) ) : ''; // Applicant's notes

        if ( ! is_email( $form_data['email'] ) ) {
             $errors->add('invalid_email', __( 'Please enter a valid email address.', 'wc-zoho-b2b' ));
        }

        // Check if email already exists as a user or in applications
        if ( email_exists( $form_data['email'] ) ) {
            $errors->add('email_exists', __( 'This email address is already registered. If you are an existing customer, please log in.', 'wc-zoho-b2b' ));
        }
        // TODO: Check if email exists in applications table with 'pending' or 'approved' status to prevent duplicates.

        if ( $errors->has_errors() ) {
            // Store errors in a transient or session to display on the form page
            // For simplicity, we'll log and not redirect/display detailed errors on frontend yet
            foreach ( $errors->get_error_messages() as $message ) {
                wczb2b_log( "B2B Application Error: {$message}", 'error' );
                // wc_add_notice( $message, 'error', 'wczb2b_application_notices' ); // If using WooCommerce notices
            }
            // Add a generic notice for now
            // wc_add_notice( __( 'There was an error with your application. Please check the fields and try again.', 'wc-zoho-b2b'), 'error', 'wczb2b_application_notices' );
            return;
        }

        // All checks passed, proceed to insert application
        global $wpdb;
        $insert_data = array(
            'company_name'   => $form_data['company_name'],
            'email'          => $form_data['email'],
            'tax_id'         => $form_data['tax_id'],
            'business_type'  => $form_data['business_type'],
            'contact_person' => $form_data['contact_person'],
            'phone'          => $form_data['phone'],
            'address'        => $form_data['address'],
            'notes'          => $form_data['notes'], // Applicant's notes
            'status'         => 'pending', // Default status
            'applied_date'   => current_time( 'mysql', true ),
        );

        // If user is logged in, associate application with user_id
        if ( is_user_logged_in() ) {
            $insert_data['user_id'] = get_current_user_id();
        }

        $result = $wpdb->insert( $this->db_applications_table_name, $insert_data );

        if ( $result === false ) {
            wczb2b_log( 'Failed to insert B2B application into database. DB Error: ' . $wpdb->last_error, 'error' );
            // wc_add_notice( __('An internal error occurred. Please try again later.', 'wc-zoho-b2b'), 'error', 'wczb2b_application_notices');
            return;
        }

        $application_id = $wpdb->insert_id;
        wczb2b_log( "B2B Application #{$application_id} submitted successfully for email: " . $form_data['email'], 'info' );

        do_action( 'wczb2b_after_application_submitted', $application_id, $form_data );

        // TODO: Send notification emails (admin and applicant)
        // TODO: Redirect user to a thank you page or display success message on the form page.
        // For now, just a log. A redirect would be:
        // wp_redirect( add_query_arg( 'application_submitted', 'success', wc_get_page_permalink('myaccount') ) ); // Or a dedicated thank you page
        // exit;
        // wc_add_notice( __('Thank you for your application! We will review it shortly.', 'wc-zoho-b2b'), 'success', 'wczb2b_application_notices');
    }

    /**
     * Process application approval from admin.
     *
     * @since 1.0.0
     */
    public function process_admin_approve_application_action() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) { // Or a more specific capability
            wp_die( esc_html__( 'You do not have sufficient permissions to perform this action.', 'wc-zoho-b2b' ) );
        }

        $application_id = isset( $_GET['application_id'] ) ? absint( $_GET['application_id'] ) : 0;
        if ( ! $application_id ) {
            wp_safe_redirect( admin_url( 'admin.php?page=wczb2b-applications&message=error&error_code=no_id' ) );
            exit;
        }

        check_admin_referer( 'wczb2b_approve_app_' . $application_id );

        $application = $this->get_application( $application_id );
        if ( ! $application || $application->status === 'approved' ) {
            wp_safe_redirect( admin_url( 'admin.php?page=wczb2b-applications&message=error&error_code=invalid_app_or_approved' ) );
            exit;
        }

        // --- Create or Update WordPress User ---
        $user_id = email_exists( $application->email );
        $user_created_now = false;

        if ( ! $user_id ) {
            // User does not exist, create one
            $password = wp_generate_password();
            $user_id = wp_create_user( $application->email, $password, $application->email );

            if ( is_wp_error( $user_id ) ) {
                wczb2b_log( "Error creating user for application #{$application_id}: " . $user_id->get_error_message(), 'error' );
                wp_safe_redirect( admin_url( 'admin.php?page=wczb2b-applications&message=error&error_code=user_create_fail' ) );
                exit;
            }
            $user_created_now = true;
            // Update user meta with details from application
            update_user_meta( $user_id, 'first_name', $application->contact_person ?: $application->company_name );
            update_user_meta( $user_id, 'billing_company', $application->company_name );
            update_user_meta( $user_id, 'billing_email', $application->email );
            update_user_meta( $user_id, 'billing_phone', $application->phone );
            // Add other relevant meta like billing_address_1, tax_id etc.
            // TODO: Send new user account notification with password.
            // wp_new_user_notification( $user_id, null, 'both' ); // Sends to user and admin
        }

        // --- Assign Approved B2B Role ---
        $user = new WP_User( $user_id );
        $approved_role = get_option( 'wc_zoho_b2b_default_user_role', 'wczb2b_approved_customer' );
        $user->set_role( $approved_role );
        // Remove pending role if it was there
        if ($user->has_cap('wczb2b_pending_customer')) {
            $user->remove_role('wczb2b_pending_customer');
        }


        // --- Update Application Status in DB ---
        global $wpdb;
        $updated = $wpdb->update(
            $this->db_applications_table_name,
            array(
                'status'         => 'approved',
                'user_id'        => $user_id, // Link to the WP user ID
                'processed_date' => current_time( 'mysql', true ),
                'processed_by'   => get_current_user_id(),
            ),
            array( 'id' => $application_id ),
            array( '%s', '%d', '%s', '%d' ), // Data formats
            array( '%d' )                    // Where format
        );

        if ( false === $updated ) {
            wczb2b_log( "Error updating application #{$application_id} status to approved. DB Error: " . $wpdb->last_error, 'error' );
            wp_safe_redirect( admin_url( 'admin.php?page=wczb2b-applications&message=error&error_code=db_update_fail' ) );
            exit;
        }

        wczb2b_log( "Application #{$application_id} approved for user ID #{$user_id}.", 'info' );

        // --- Trigger Action Hooks & Notifications ---
        do_action( 'wczb2b_application_status_changed', $application_id, 'approved', $application->status );
        do_action( 'wczb2b_user_approved', $user_id, $application_id, $application );
        // TODO: Send email notification to the applicant about approval.

        wp_safe_redirect( admin_url( 'admin.php?page=wczb2b-applications&message=approved&app_id=' . $application_id ) );
        exit;
    }

    /**
     * Process application rejection from admin.
     *
     * @since 1.0.0
     */
    public function process_admin_reject_application_action() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to perform this action.', 'wc-zoho-b2b' ) );
        }

        $application_id = isset( $_GET['application_id'] ) ? absint( $_GET['application_id'] ) : 0;
        if ( ! $application_id ) {
            wp_safe_redirect( admin_url( 'admin.php?page=wczb2b-applications&message=error&error_code=no_id_reject' ) );
            exit;
        }

        check_admin_referer( 'wczb2b_reject_app_' . $application_id );

        $application = $this->get_application( $application_id );
        if ( ! $application || $application->status === 'rejected' ) {
             wp_safe_redirect( admin_url( 'admin.php?page=wczb2b-applications&message=error&error_code=invalid_app_or_rejected' ) );
            exit;
        }

        global $wpdb;
        $updated = $wpdb->update(
            $this->db_applications_table_name,
            array(
                'status'         => 'rejected',
                'processed_date' => current_time( 'mysql', true ),
                'processed_by'   => get_current_user_id(),
            ),
            array( 'id' => $application_id ),
            array( '%s', '%s', '%d' ), // Data formats
            array( '%d' )              // Where format
        );

        if ( false === $updated ) {
            wczb2b_log( "Error updating application #{$application_id} status to rejected. DB Error: " . $wpdb->last_error, 'error' );
            wp_safe_redirect( admin_url( 'admin.php?page=wczb2b-applications&message=error&error_code=db_update_fail_reject' ) );
            exit;
        }

        wczb2b_log( "Application #{$application_id} rejected.", 'info' );

        do_action( 'wczb2b_application_status_changed', $application_id, 'rejected', $application->status );
        do_action( 'wczb2b_user_rejected', $application_id, $application->user_id, $application ); // Pass user_id if it was linked
        // TODO: Send email notification to the applicant about rejection.

        wp_safe_redirect( admin_url( 'admin.php?page=wczb2b-applications&message=rejected&app_id=' . $application_id ) );
        exit;
    }

    /**
     * Get application data by ID.
     * @param int $application_id
     * @return object|null Application data as an object, or null if not found.
     */
    public function get_application( $application_id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->db_applications_table_name} WHERE id = %d", $application_id ) );
    }

    /**
     * Get applications based on status or other criteria.
     * @param array $args Query arguments (e.g., 'status' => 'pending', 'number' => 10, 'offset' => 0).
     * @return array Array of application objects.
     */
    public function get_applications( $args = array() ) {
        global $wpdb;
        $defaults = array(
            'status' => 'pending',
            'number' => 20,
            'offset' => 0,
            'orderby' => 'applied_date',
            'order' => 'DESC',
        );
        $args = wp_parse_args( $args, $defaults );

        $sql = "SELECT * FROM {$this->db_applications_table_name}";
        $where_clauses = array();
        if ( ! empty( $args['status'] ) ) {
            $where_clauses[] = $wpdb->prepare( "status = %s", $args['status'] );
        }
        // Add more WHERE clauses if needed (e.g., search by company name, email)

        if ( ! empty( $where_clauses ) ) {
            $sql .= " WHERE " . implode( ' AND ', $where_clauses );
        }
        $sql .= $wpdb->prepare( " ORDER BY {$args['orderby']} {$args['order']} LIMIT %d OFFSET %d", $args['number'], $args['offset'] );

        return $wpdb->get_results( $sql );
    }

    /**
     * Count total applications, optionally by status.
     * @param string $status (Optional) Status to count.
     * @return int Total count.
     */
    public function count_applications( $status = '' ) {
        global $wpdb;
        $sql = "SELECT COUNT(*) FROM {$this->db_applications_table_name}";
        if ( ! empty( $status ) ) {
            $sql .= $wpdb->prepare( " WHERE status = %s", $status );
        }
        return (int) $wpdb->get_var( $sql );
    }
}
?>
