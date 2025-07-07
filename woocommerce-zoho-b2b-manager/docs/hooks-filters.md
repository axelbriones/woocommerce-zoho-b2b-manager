# Hooks & Filters Reference - WooCommerce Zoho B2B Manager

This document provides a comprehensive list of action hooks and filter hooks available in the WooCommerce Zoho B2B Manager plugin. These allow developers to customize and extend the plugin's functionality without modifying core files.

## Action Hooks

Action hooks allow you to execute custom code at specific points during the plugin's execution.

### User Management & Application

*   **`wczb2b_before_application_form`** (Not explicitly added yet, but good for future)
    *   Fires before the B2B application form is rendered by the shortcode.
    *   **Usage:** `do_action( 'wczb2b_before_application_form' );`
    *   **Example:** Add custom content or scripts before the form.

*   **`wczb2b_after_application_form_fields`** (Not explicitly added yet, but good for future)
    *   Fires inside the B2B application form HTML, after the default fields but before the submit button.
    *   **Usage:** `do_action( 'wczb2b_after_application_form_fields' );`
    *   **Example:** Add custom form fields to the application.

*   **`wczb2b_after_application_submitted`**
    *   Fires after a B2B application form has been successfully submitted and basic processing (data insertion into custom table) is done.
    *   **Location:** `WC_Zoho_B2B_User_Manager::maybe_handle_application_form_submission()`
    *   **Parameters:**
        *   `$application_id` (int): The ID of the newly created application entry in `wp_wc_zoho_b2b_applications` table.
        *   `$form_data` (array): Sanitized data submitted from the form.
    *   **Usage:** `do_action( 'wczb2b_after_application_submitted', $application_id, $form_data );`
    *   **Example:** Send a custom admin notification or trigger additional non-Zoho related actions.

*   **`wczb2b_application_status_changed`**
    *   Fires when a B2B application's status changes (e.g., from pending to approved, or pending to rejected).
    *   **Location:** `WC_Zoho_B2B_User_Manager::process_admin_approve_application_action()`, `WC_Zoho_B2B_User_Manager::process_admin_reject_application_action()`
    *   **Parameters:**
        *   `$application_id` (int): The ID of the application.
        *   `$new_status` (string): The new status (e.g., 'approved', 'rejected').
        *   `$old_status` (string): The old status (e.g., 'pending').
    *   **Usage:** `do_action( 'wczb2b_application_status_changed', $application_id, $new_status, $old_status );`

*   **`wczb2b_user_approved`**
    *   Fires specifically when a B2B user's application is approved. This happens after the WP user is created/role updated, and application status in DB is set to 'approved'.
    *   **Location:** `WC_Zoho_B2B_User_Manager::process_admin_approve_application_action()`
    *   **Parameters:**
        *   `$user_id` (int): The WordPress User ID of the approved applicant.
        *   `$application_id` (int): The ID of the application from `wp_wc_zoho_b2b_applications`.
        *   `$application_data` (object): The full application data object from the database.
    *   **Usage:** `do_action( 'wczb2b_user_approved', $user_id, $application_id, $application_data );`
    *   **Example:** Used by `WC_Zoho_B2B_Zoho_Integration` to trigger synchronization with Zoho CRM.

*   **`wczb2b_user_rejected`**
    *   Fires specifically when a B2B user's application is rejected.
    *   **Location:** `WC_Zoho_B2B_User_Manager::process_admin_reject_application_action()`
    *   **Parameters:**
        *   `$application_id` (int): The ID of the application.
        *   `$user_id` (int|null): The WordPress User ID if one was associated with the application, or null.
        *   `$application_data` (object): The full application data object from the database.
    *   **Usage:** `do_action( 'wczb2b_user_rejected', $application_id, $user_id, $application_data );`

### Pricing & Product

*   **`wczb2b_before_product_b2b_fields`** (Admin - Placeholder for future use)
    *   Fires in the product edit screen before B2B specific fields (like fixed B2B price) would be displayed.
    *   **Parameters:** `$product_id` (int).
    *   **Usage:** `do_action( 'wczb2b_before_product_b2b_fields', $product_id );`

*   **`wczb2b_after_product_b2b_fields`** (Admin - Placeholder for future use)
    *   Fires in the product edit screen after B2B specific fields would be displayed.
    *   **Parameters:** `$product_id` (int).
    *   **Usage:** `do_action( 'wczb2b_after_product_b2b_fields', $product_id );`

### Wishlist

*   **`wczb2b_before_wishlist_table`** (Not explicitly added yet, but good for future)
    *   Fires before the wishlist table is rendered by the shortcode.
    *   **Usage:** `do_action( 'wczb2b_before_wishlist_table' );`

*   **`wczb2b_after_wishlist_table`** (Not explicitly added yet, but good for future)
    *   Fires after the wishlist table is rendered by the shortcode.
    *   **Usage:** `do_action( 'wczb2b_after_wishlist_table' );`

*   **`wczb2b_added_to_wishlist`**
    *   Fires after an item is successfully added to the wishlist database.
    *   **Location:** `WC_Zoho_B2B_Wishlist_Manager::add_item()`
    *   **Parameters:** `$product_id` (int), `$variation_id` (int), `$user_id` (int).
    *   **Usage:** `do_action( 'wczb2b_added_to_wishlist', $product_id, $variation_id, $user_id );`

*   **`wczb2b_removed_from_wishlist`**
    *   Fires after an item is successfully removed from the wishlist database.
    *   **Location:** `WC_Zoho_B2B_Wishlist_Manager::remove_item()`
    *   **Parameters:** `$product_id` (int), `$variation_id` (int), `$user_id` (int).
    *   **Usage:** `do_action( 'wczb2b_removed_from_wishlist', $product_id, $variation_id, $user_id );`

### Zoho Integration

*   **`wczb2b_before_user_sync_zoho`** (Placeholder for future use)
    *   Fires before a B2B user's data is compiled and sent to Zoho.
    *   **Parameters:** `$user_id` (int), `$application_data` (object).
    *   **Usage:** `do_action( 'wczb2b_before_user_sync_zoho', $user_id, $application_data );`

*   **`wczb2b_after_user_sync_zoho`** (Placeholder for future use)
    *   Fires after a B2B user's data has been synced to Zoho.
    *   **Parameters:** `$user_id` (int), `$zoho_account_id` (string|null), `$zoho_contact_id` (string|null), `$response` (mixed - response from Zoho or WP_Error).
    *   **Usage:** `do_action( 'wczb2b_after_user_sync_zoho', $user_id, $zoho_account_id, $zoho_contact_id, $response );`

*(More hooks will be added as features are developed.)*

## Filter Hooks

Filter hooks allow you to modify data used by the plugin.

### User Management & Application

*   **`wczb2b_application_form_fields`** (Placeholder for future use)
    *   Filters the array of fields definition for the B2B application form, allowing addition/modification/removal.
    *   **Parameters:** `$fields` (array).
    *   **Usage:** `apply_filters( 'wczb2b_application_form_fields', $fields );`

*   **`wczb2b_b2b_user_roles`**
    *   Filters the list of roles considered as "B2B roles" by the `wczb2b_is_b2b_customer()` helper function.
    *   **Location:** `includes/functions.php` (inside `wczb2b_is_b2b_customer()`)
    *   **Parameters:** `$roles` (array) - Array of role slugs (e.g., `['wczb2b_approved_customer']`).
    *   **Usage:** `apply_filters( 'wczb2b_get_b2b_user_roles', $roles );` (Note: the filter name in `functions.php` is `wczb2b_get_b2b_user_roles`)

*   **`wczb2b_new_b2b_user_data`** (Placeholder for future use)
    *   Filters the user data array before a new WordPress user is created from a B2B application.
    *   **Parameters:** `$user_data` (array) - Data for `wp_insert_user()`.
        *   `$application_data` (object) - Original application data from the database.
    *   **Usage:** `apply_filters( 'wczb2b_new_b2b_user_data', $user_data, $application_data );`

### Pricing & Product

*   **`wczb2b_calculated_b2b_price`**
    *   Filters the final B2B price for a product after the plugin's internal logic (fixed price or percentage discount) has been applied.
    *   **Location:** `WC_Zoho_B2B_Pricing_Manager::calculate_b2b_price_for_product()`
    *   **Parameters:**
        *   `$final_price` (float): The B2B price calculated by the plugin.
        *   `$original_price_passed_to_calc` (float): The price input to the calculation (could be sale or regular).
        *   `$product` (WC_Product): The product object.
        *   `$pricing_method` (string): The B2B pricing method used ('fixed_price' or 'percentage_discount').
    *   **Usage:** `apply_filters( 'wczb2b_calculated_b2b_price', $final_price, $original_price_passed_to_calc, $product, $pricing_method );`

*   **`wczb2b_display_price_html`** (Not actively used for modification yet, WC rebuilds HTML)
    *   Filters the HTML for displaying product prices for B2B customers. WooCommerce typically rebuilds this based on filtered `get_price`, `get_regular_price`, `get_sale_price`.
    *   **Parameters:**
        *   `$price_html` (string): The generated price HTML.
        *   `$product` (WC_Product): The product object.
    *   **Usage:** `apply_filters( 'wczb2b_display_price_html', $price_html, $product );`

*   **`wczb2b_minimum_quantity`** (Placeholder for future use)
    *   Filters the minimum purchase quantity for a product for B2B customers.
    *   **Parameters:**
        *   `$min_quantity` (int): The minimum quantity.
        *   `$product` (WC_Product): The product object.
        *   `$user_id` (int): The B2B customer's user ID.
    *   **Usage:** `apply_filters( 'wczb2b_minimum_quantity', $min_quantity, $product, $user_id );`

*   **`wczb2b_product_is_purchasable_for_b2b`** (Placeholder for future use)
    *   Filters whether a product is purchasable by the current B2B user, beyond standard WooCommerce checks.
    *   **Parameters:**
        *   `$is_purchasable` (bool).
        *   `$product` (WC_Product).
    *   **Usage:** `apply_filters( 'wczb2b_product_is_purchasable_for_b2b', $is_purchasable, $product );`

*   **`wczb2b_allow_guest_wishlist`**
    *   Filters whether to allow guests (non-logged-in users) to use the wishlist feature. Defaults to `false`.
    *   **Location:** `WC_Zoho_B2B_Wishlist_Manager` (used in various methods)
    *   **Parameters:** `$allow` (bool).
    *   **Usage:** `apply_filters( 'wczb2b_allow_guest_wishlist', false );`


### Zoho Integration

*   **`wczb2b_zoho_user_sync_data`** (Placeholder for future use)
    *   Filters the data array prepared to be sent to Zoho when syncing a B2B user (as Account and Contact).
    *   **Parameters:**
        *   `$account_payload` (array): Data payload for creating/updating Zoho Account.
        *   `$contact_payload` (array): Data payload for creating/updating Zoho Contact.
        *   `$user_id` (int): The WordPress User ID.
        *   `$application_data` (object|null): Application data from the database.
    *   **Usage:** `apply_filters( 'wczb2b_zoho_user_sync_data', array('account' => $account_payload, 'contact' => $contact_payload), $user_id, $application_data );` (Developer would then access `$payload_array['account']` and `$payload_array['contact']`).

*   **`wczb2b_zoho_order_sync_data`** (Placeholder for future use)
    *   Filters the data array prepared to be sent to Zoho when syncing a B2B order.
    *   **Parameters:**
        *   `$zoho_data` (array): Data prepared for Zoho (e.g., for Sales Order in Zoho Books/Inventory).
        *   `$order` (WC_Order): The WooCommerce order object.
    *   **Usage:** `apply_filters( 'wczb2b_zoho_order_sync_data', $zoho_data, $order );`

*(This list will be updated as the plugin develops.)*
