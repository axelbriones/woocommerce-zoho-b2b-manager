<?php
/**
 * Provides the view for the B2B application form.
 * This template can be loaded via a shortcode.
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

// Pre-fill some fields if user is logged in
$current_user = wp_get_current_user();
$current_user_id = $current_user->ID;
$current_email = $current_user_id ? $current_user->user_email : '';
$current_contact_person = $current_user_id ? trim($current_user->first_name . ' ' . $current_user->last_name) : '';
if (empty(trim($current_contact_person)) && $current_user_id) { // Fallback to display name if first/last are empty
    $current_contact_person = $current_user->display_name;
}
$current_phone = $current_user_id ? get_user_meta($current_user_id, 'billing_phone', true) : '';
$current_company = $current_user_id ? get_user_meta($current_user_id, 'billing_company', true) : '';


// Retrieve form data if it was previously submitted and there was an error (for re-population)
// This is a basic example; a more robust solution might use WP Session or a custom handler.
$form_values = array();
// if (isset($_SESSION['wczb2b_form_values'])) {
// $form_values = $_SESSION['wczb2b_form_values'];
// unset($_SESSION['wczb2b_form_values']);
// }

$company_name_val   = isset($form_values['wczb2b_company_name']) ? $form_values['wczb2b_company_name'] : $current_company;
$email_val          = isset($form_values['wczb2b_email']) ? $form_values['wczb2b_email'] : $current_email;
$contact_person_val = isset($form_values['wczb2b_contact_person']) ? $form_values['wczb2b_contact_person'] : $current_contact_person;
$phone_val          = isset($form_values['wczb2b_phone']) ? $form_values['wczb2b_phone'] : $current_phone;
$tax_id_val         = isset($form_values['wczb2b_tax_id']) ? $form_values['wczb2b_tax_id'] : '';
$business_type_val  = isset($form_values['wczb2b_business_type']) ? $form_values['wczb2b_business_type'] : '';
$address_val        = isset($form_values['wczb2b_address']) ? $form_values['wczb2b_address'] : '';
$notes_val          = isset($form_values['wczb2b_applicant_notes']) ? $form_values['wczb2b_applicant_notes'] : '';

?>

<div class="wczb2b-application-form-wrapper woocommerce">
    <h2><?php esc_html_e( 'B2B Customer Application', 'wc-zoho-b2b' ); ?></h2>

    <?php
    // Messages are now displayed by the shortcode handler in class-frontend.php using transients
    // Example: if form was submitted via GET and has query args for status:
    // if (isset($_GET['application_submitted']) && $_GET['application_submitted'] === 'success') {
    // echo '<div class="woocommerce-message" role="alert">' . esc_html__('Thank you for your application! We will review it shortly.', 'wc-zoho-b2b') . '</div>';
    // } elseif (isset($_GET['application_error'])) {
    //     $error_code = sanitize_key($_GET['application_error']);
    //     $error_message = __('An error occurred. Please try again.', 'wc-zoho-b2b');
    //     if ($error_code === 'field_required') $error_message = __('Please fill in all required fields.', 'wc-zoho-b2b');
    //     if ($error_code === 'invalid_email') $error_message = __('Invalid email address.', 'wc-zoho-b2b');
    //     echo '<div class="woocommerce-error" role="alert">' . esc_html($error_message) . '</div>';
    // }
    ?>

    <form id="wczb2b-application-form" class="wczb2b-form" method="post" action="<?php echo esc_url( get_permalink() ); // Submit to the same page to handle messages easily ?>">

        <p class="form-row form-row-wide validate-required" id="wczb2b_company_name_field">
            <label for="wczb2b_company_name"><?php esc_html_e( 'Company Name', 'wc-zoho-b2b' ); ?>&nbsp;<span class="required">*</span></label>
            <input type="text" class="input-text" name="wczb2b_company_name" id="wczb2b_company_name" value="<?php echo esc_attr( $company_name_val ); ?>" required />
        </p>

        <p class="form-row form-row-wide validate-required validate-email" id="wczb2b_email_field">
            <label for="wczb2b_email"><?php esc_html_e( 'Email Address', 'wc-zoho-b2b' ); ?>&nbsp;<span class="required">*</span></label>
            <input type="email" class="input-text" name="wczb2b_email" id="wczb2b_email" value="<?php echo esc_attr( $email_val ); ?>" required <?php if( $current_user_id && $current_email ) echo 'readonly="readonly"'; // Prevent editing if logged in and email known ?> />
        </p>

        <p class="form-row form-row-first validate-required" id="wczb2b_contact_person_field">
            <label for="wczb2b_contact_person"><?php esc_html_e( 'Contact Person', 'wc-zoho-b2b' ); ?>&nbsp;<span class="required">*</span></label>
            <input type="text" class="input-text" name="wczb2b_contact_person" id="wczb2b_contact_person" value="<?php echo esc_attr( $contact_person_val ); ?>" required />
        </p>

        <p class="form-row form-row-last validate-required" id="wczb2b_phone_field">
            <label for="wczb2b_phone"><?php esc_html_e( 'Phone Number', 'wc-zoho-b2b' ); ?>&nbsp;<span class="required">*</span></label>
            <input type="tel" class="input-text" name="wczb2b_phone" id="wczb2b_phone" value="<?php echo esc_attr( $phone_val ); ?>" required />
        </p>
        <div class="clear"></div>

        <p class="form-row form-row-first" id="wczb2b_tax_id_field">
            <label for="wczb2b_tax_id"><?php esc_html_e( 'Tax ID / VAT Number', 'wc-zoho-b2b' ); ?></label>
            <input type="text" class="input-text" name="wczb2b_tax_id" id="wczb2b_tax_id" value="<?php echo esc_attr( $tax_id_val ); ?>" />
        </p>

        <p class="form-row form-row-last" id="wczb2b_business_type_field">
            <label for="wczb2b_business_type"><?php esc_html_e( 'Type of Business', 'wc-zoho-b2b' ); ?></label>
            <input type="text" class="input-text" name="wczb2b_business_type" id="wczb2b_business_type" value="<?php echo esc_attr( $business_type_val ); ?>" />
        </p>
        <div class="clear"></div>

        <p class="form-row form-row-wide" id="wczb2b_address_field">
            <label for="wczb2b_address"><?php esc_html_e( 'Company Address', 'wc-zoho-b2b' ); ?></label>
            <textarea class="input-text" name="wczb2b_address" id="wczb2b_address" rows="4"><?php echo esc_textarea( $address_val ); ?></textarea>
        </p>

        <p class="form-row form-row-wide" id="wczb2b_applicant_notes_field">
            <label for="wczb2b_applicant_notes"><?php esc_html_e( 'Notes (Optional)', 'wc-zoho-b2b' ); ?></label>
            <textarea class="input-text" name="wczb2b_applicant_notes" id="wczb2b_applicant_notes" rows="3"><?php echo esc_textarea( $notes_val ); ?></textarea>
        </p>

        <?php // Honeypot field for basic spam protection ?>
        <p class="wczb2b-hp" style="opacity: 0; position: absolute; top: 0; left: -9999px; z-index: -1; pointer-events: none;" aria-hidden="true">
            <label for="wczb2b_hp_email"><?php esc_html_e( 'Leave this field empty', 'wc-zoho-b2b'); ?></label>
            <input type="email" name="wczb2b_hp_email" id="wczb2b_hp_email" tabindex="-1" autocomplete="off" />
        </p>

        <?php wp_nonce_field( 'wczb2b_submit_application_action', 'wczb2b_application_nonce_field' ); ?>

        <p class="form-row">
            <input type="submit" class="button" name="wczb2b_submit_application_button" value="<?php esc_attr_e( 'Submit Application', 'wc-zoho-b2b' ); ?>" />
        </p>
    </form>
</div>
