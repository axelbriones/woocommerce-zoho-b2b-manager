# API Reference - WooCommerce Zoho B2B Manager

This document outlines the classes, methods, hooks, and filters available for developers to extend or interact with the WooCommerce Zoho B2B Manager plugin.

## Plugin Structure Overview

*   **`woocommerce-zoho-b2b-manager.php`**: Main plugin file, initialization.
*   **`includes/`**: Core PHP classes.
    *   `class-admin.php`: Admin area functionality, settings pages.
    *   `class-frontend.php`: Frontend display logic, shortcodes.
    *   `class-user-manager.php`: B2B user registration, applications, roles.
    *   `class-pricing-manager.php`: B2B pricing rules and display.
    *   `class-product-manager.php`: B2B product visibility, custom fields, quantities.
    *   `class-order-manager.php`: B2B order specific logic.
    *   `class-wishlist-manager.php`: Wishlist features.
    *   `class-zoho-integration.php`: Handles communication with Zoho API for B2B data.
    *   `class-compatibility.php`: Manages compatibility with other plugins.
    *   `functions.php`: Global helper functions.
*   **`admin/partials/`**: HTML templates for admin settings pages.
*   **`public/partials/`**: HTML templates for frontend elements (forms, wishlist display).
*   **`templates/`**: WooCommerce templates that can be overridden in themes.

## Key Classes and Methods

## Key Classes and Methods

### `WooCommerce_Zoho_B2B_Manager` (Main Plugin Class)
*Located in `woocommerce-zoho-b2b-manager.php`*
*   `get_instance()`: Access the singleton instance of the main plugin class.
*   `compatibility`: Public property holding the `WC_Zoho_B2B_Compatibility` instance.
*   `admin`, `frontend`, `user_manager`, `pricing_manager`, `product_manager`, `order_manager`, `wishlist_manager`, `zoho_integration`: Public properties holding instances of their respective manager classes.

### `WC_Zoho_B2B_Installer`
*Located in `includes/class-installer.php`*
*   `activate()`: Static method called on plugin activation. Creates tables, registers roles, sets default options.
*   `deactivate()`: Static method called on plugin deactivation.
*   `create_tables()`: (Private) Creates `wp_wc_zoho_b2b_applications` and `wp_wc_zoho_b2b_wishlist` tables.
*   `register_user_roles()`: (Private) Registers `wczb2b_pending_customer` and `wczb2b_approved_customer` roles.
*   `set_default_options()`: (Private) Sets initial plugin options.

### `WC_Zoho_B2B_Compatibility`
*Located in `includes/class-compatibility.php`*
*   `get_instance()`: Access singleton instance.
*   `is_main_zoho_plugin_active()`: Checks if `woocommerce-zoho-integration/woocommerce-zoho-integration.php` is active.
*   `get_main_zoho_plugin_version()`: Gets version of the main Zoho plugin, if active.
*   `get_main_zoho_plugin_api_credentials()`: Attempts to get API credentials (Client ID, Secret, Domain) from the main Zoho plugin's options. (Relies on guessed option names and keys).
*   `get_main_zoho_plugin_token_details()`: Attempts to get OAuth tokens from the main Zoho plugin's options. (Relies on guessed option names and keys).

### `WC_Zoho_B2B_Admin`
*Located in `includes/class-admin.php`*
*   `add_plugin_admin_menu()`: Adds the "Zoho B2B" menu and submenus to the WordPress admin.
*   `register_plugin_settings()`: Registers plugin settings groups and fields using the Settings API.
*   `display_general_settings_page()`, `display_applications_page()`, `display_roles_pricing_settings_page()`, `display_zoho_settings_page()`: Callbacks to render the admin page content (loads partials).
*   `handle_zoho_oauth_callback()`: (Private) Handles the OAuth callback from Zoho to exchange authorization code for tokens.
*   Render callbacks for various settings fields (e.g., `render_select_field`, `render_radio_field`).

### `WC_Zoho_B2B_User_Manager`
*Located in `includes/class-user-manager.php`*
*   `get_instance()`: Access singleton instance.
*   `maybe_handle_application_form_submission()`: Processes submissions from the B2B application form. Validates data, creates an application entry in the custom table.
*   `process_admin_approve_application_action()`: Handles the admin action to approve a B2B application. Creates/updates WP user, changes role, updates application status, triggers hooks.
*   `process_admin_reject_application_action()`: Handles the admin action to reject a B2B application. Updates application status, triggers hooks.
*   `get_application( $application_id )`: Retrieves a single application by its ID.
*   `get_applications( $args = array() )`: Retrieves a list of applications based on arguments (status, pagination, etc.).
*   `count_applications( $status = '' )`: Counts applications, optionally filtered by status.

### `WC_Zoho_B2B_Frontend`
*Located in `includes/class-frontend.php`*
*   `get_instance()`: Access singleton instance.
*   `render_application_form_shortcode( $atts )`: Renders the B2B application form via the `[wc_zoho_b2b_application_form]` shortcode. Handles display of status messages.
*   `render_wishlist_shortcode( $atts )`: Renders the user's wishlist via the `[wc_zoho_b2b_wishlist]` shortcode.
*   `display_wishlist_button_product_page()`: Displays the "Add to Wishlist" button on single product pages.
*   `get_wishlist_button_html( $product_id, $variation_id = 0 )`: Generates the HTML for the wishlist button (used on product pages and AJAX responses).

### `WC_Zoho_B2B_Wishlist_Manager`
*Located in `includes/class-wishlist-manager.php`*
*   `get_instance()`: Access singleton instance.
*   `is_enabled()`: Checks if the wishlist feature is enabled in settings.
*   `add_item( $user_id, $product_id, $variation_id = 0 )`: Adds an item to a user's wishlist.
*   `remove_item( $user_id, $product_id, $variation_id = 0 )`: Removes an item from a user's wishlist.
*   `get_wishlist_items( $user_id )`: Retrieves all items from a user's wishlist.
*   `is_in_wishlist( $user_id, $product_id, $variation_id = 0 )`: Checks if a specific item is in the user's wishlist.
*   `count_items( $user_id )`: Counts items in a user's wishlist.
*   `ajax_handle_add_to_wishlist()`: AJAX handler for adding items.
*   `ajax_handle_remove_from_wishlist()`: AJAX handler for removing items.

### `WC_Zoho_B2B_Zoho_Integration`
*Located in `includes/class-zoho-integration.php`*
*   `get_instance()`: Access singleton instance.
*   `load_settings()`: Loads API credentials and tokens, attempting to use main plugin's config if enabled.
*   `get_client_id()`, `get_client_secret()`, `get_zoho_domain()`, `get_access_token()`: Getters for API configuration.
*   `is_b2b_zoho_enabled()`: Checks if B2B Zoho features are globally enabled.
*   `is_client_fully_configured()`: Checks if necessary API credentials are set.
*   `has_valid_access_token( $check_expiry = true )`: Checks for access token and its validity.
*   `update_tokens( $access_token, $expires_in, $refresh_token = null )`: Saves new tokens.
*   `get_redirect_uri()`: Returns the OAuth redirect URI for this plugin.
*   `get_api_base_url( $service = 'crm', $version = 'v2' )`: Constructs Zoho API base URL for a given service.
*   `refresh_access_token()`: Attempts to refresh an expired access token using the refresh token.
*   `make_api_request( $service, $endpoint, $method = 'GET', $data = array(), $headers = array() )`: Makes a generic request to the Zoho API.
*   `schedule_user_sync_to_zoho( $user_id, $application_id, $application_data )`: Hook callback to initiate user sync.
*   `sync_b2b_user_to_zoho_crm( $user_id, $application )`: Syncs an approved B2B user to Zoho CRM as an Account and Contact. Includes basic duplicate checking.

### `WC_Zoho_B2B_Pricing_Manager`
*Located in `includes/class-pricing-manager.php`*
*   `get_instance()`: Access singleton instance.
*   `get_b2b_price( $price, $product )`: Filters the product's active price for B2B users.
*   `get_b2b_regular_price( $regular_price, $product )`: Filters the product's regular price for B2B users.
*   `get_b2b_sale_price( $sale_price, $product )`: Filters the product's sale price for B2B users.
*   `b2b_price_html( $price_html, $product )`: Filters the price HTML string (currently lets WC rebuild it).
*   `apply_b2b_price_to_cart_items( $cart )`: Ensures B2B prices are applied to items in the cart.
*   `calculate_b2b_price_for_product( $current_price, $product, $force_regular = false )`: (Private) Core logic to calculate B2B price based on role discounts.

## Helper Functions
*Located in `includes/functions.php`*
*   `wczb2b_is_b2b_customer( $user_id = null )`: Checks if a user has a designated B2B role (e.g., `wczb2b_approved_customer`).
*   `wczb2b_get_option( $option_name, $default_value = '' )`: Helper to get plugin options with `wc_zoho_b2b_` prefix. (Note: Most options are now retrieved directly with `get_option()` using constants for keys).
*   `wczb2b_log( $message, $level = 'info' )`: Logs messages using WC_Logger or error_log.

## Database Tables
*   **`{$wpdb->prefix}wc_zoho_b2b_applications`**: Stores B2B application data.
    *   `id` (BIGINT, PK, AI)
    *   `user_id` (BIGINT UNSIGNED, NULL): Linked WordPress user ID after approval/creation.
    *   `company_name` (VARCHAR(255), NOT NULL)
    *   `tax_id` (VARCHAR(50), NULL)
    *   `business_type` (VARCHAR(100), NULL)
    *   `contact_person` (VARCHAR(255), NULL)
    *   `phone` (VARCHAR(50), NULL)
    *   `email` (VARCHAR(100), NOT NULL)
    *   `address` (TEXT, NULL)
    *   `status` (VARCHAR(20), NOT NULL, DEFAULT 'pending'): e.g., 'pending', 'approved', 'rejected'.
    *   `zoho_contact_id` (VARCHAR(50), NULL): Stores the ID from Zoho CRM Contact module.
    *   `applied_date` (DATETIME, NOT NULL, DEFAULT CURRENT_TIMESTAMP)
    *   `processed_date` (DATETIME, NULL): Date the application was approved/rejected.
    *   `processed_by` (BIGINT UNSIGNED, NULL): Admin user ID who processed the application.
    *   `notes` (TEXT, NULL): Internal notes by admin or from applicant.
*   **`{$wpdb->prefix}wc_zoho_b2b_wishlist`**: Stores user wishlist items.
    *   `id` (BIGINT, PK, AI)
    *   `user_id` (BIGINT UNSIGNED, NOT NULL)
    *   `product_id` (BIGINT UNSIGNED, NOT NULL)
    *   `variation_id` (BIGINT UNSIGNED, DEFAULT 0)
    *   `date_added` (DATETIME, NOT NULL, DEFAULT CURRENT_TIMESTAMP)

*(This document will evolve as the plugin is developed further.)*
