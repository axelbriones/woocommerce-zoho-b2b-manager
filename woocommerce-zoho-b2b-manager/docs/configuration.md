# Configuration Guide - WooCommerce Zoho B2B Manager

This guide explains how to configure the WooCommerce Zoho B2B Manager plugin after installation.

## Accessing Settings

The main settings for the plugin can be found in your WordPress admin dashboard under the menu:

**Zoho B2B**

This menu contains the following sub-pages:

*   **General Settings** (`wp-admin/admin.php?page=wczb2b-settings`)
*   **Applications** (`wp-admin/admin.php?page=wczb2b-applications`)
*   **User Roles & Pricing** (`wp-admin/admin.php?page=wczb2b-roles-pricing-settings`)
*   **Zoho Integration** (`wp-admin/admin.php?page=wczb2b-zoho-settings`)

---

## 1. General Settings

This page allows you to configure the basic operational parameters of the B2B functionality.

*   **B2B Registration Mode:**
    *   `Manual Approval by Admin` (Default): New B2B applicants must be manually approved by an administrator via the "Applications" page.
    *   `Automatic Approval`: New B2B applicants are automatically approved and assigned the designated B2B role.
    *   `Invitation Only` (Future Feature): Users can only become B2B customers via an invitation.
    *   *Option Name:* `wc_zoho_b2b_registration_mode`

*   **Show Prices to Guests:**
    *   `Yes`: Product prices are visible to all users, including non-logged-in guests.
    *   `No` (Default): Product prices are hidden from non-logged-in users. B2B pricing will only be shown to authenticated B2B customers.
    *   *Option Name:* `wc_zoho_b2b_show_prices_logged_out`

*   **Approved B2B User Role:**
    *   Allows selection of the WordPress role that will be assigned to users upon successful B2B application approval.
    *   Defaults to `wczb2b_approved_customer` (B2B Customer). Other roles created by this plugin or other plugins will be listed.
    *   *Option Name:* `wc_zoho_b2b_default_user_role`

*   **Enable B2B Wishlist:**
    *   `Yes` (Default): Enables the wishlist functionality for users.
    *   `No`: Disables the wishlist feature.
    *   *Option Name:* `wc_zoho_b2b_enable_wishlist`

---

## 2. Applications

This page (`wp-admin/admin.php?page=wczb2b-applications`) lists all submitted B2B applications.

*   **View Applications:** Applications are displayed in a table with details like Company Name, Email, Contact Person, Status, and Applied Date.
*   **Filter:** You can filter applications by status (All, Pending, Approved, Rejected).
*   **Search:** Search applications by company name, email, or contact person.
*   **Actions:**
    *   **Approve:** For "Pending" applications. This will change the application status to "Approved", create a WordPress user if one doesn't exist, assign the "Approved B2B User Role", and trigger synchronization with Zoho if enabled.
    *   **Reject:** For "Pending" applications. This will change the application status to "Rejected".
    *   (Future: View Details, Edit Application Notes)

---

## 3. User Roles & Pricing Settings

This page configures how pricing is determined and displayed for B2B customers.

*   **B2B Pricing Method:**
    *   `Percentage Discount by Role` (Default): Apply a percentage discount off the product price based on the B2B user's role. Discounts are defined below.
    *   `Fixed Price per Product (via Product Edit)`: (Future Feature) Allows setting a specific B2B price for each product directly on the WooCommerce product edit screen.
    *   *Option Name:* `wc_zoho_b2b_pricing_method`

*   **Role-Based Discounts (%):**
    *   If "Percentage Discount by Role" is selected, this section allows you to define a discount percentage for each relevant WordPress user role (especially the B2B roles like "B2B Customer").
    *   Enter a numerical value for the discount (e.g., `10` for 10%). Leave blank or `0` for no discount for a specific role.
    *   *Option Name:* `wc_zoho_b2b_role_discounts` (This is an array where keys are role slugs and values are discount percentages).

---

## 4. Zoho Integration Settings

Configure the connection and synchronization with your Zoho services for B2B specific data.

*   **Use Main Integration's Config:**
    *   If the `woocommerce-zoho-integration` plugin by axelbriones is active, you can check this box to attempt to use its API credentials and tokens.
    *   If unchecked, or if the main plugin is not active/configured, you must provide the credentials below.
    *   *Option Name:* `wc_zoho_b2b_use_main_plugin_config`

*   **Enable B2B Zoho Features:**
    *   Master switch to enable or disable all B2B specific Zoho synchronization features of this plugin.
    *   *Option Name:* `wc_zoho_b2b_zoho_enabled`

*   **Zoho Account Domain:**
    *   Select the domain of your Zoho account (e.g., .com, .eu, .in). This determines the API endpoints.
    *   *Option Name:* `wc_zoho_b2b_zoho_domain`

*   **Client ID:**
    *   Your OAuth Client ID obtained from Zoho API Console for this application.
    *   *Option Name:* `wczb2b_client_id`

*   **Client Secret:**
    *   Your OAuth Client Secret obtained from Zoho API Console.
    *   *Option Name:* `wczb2b_client_secret`

*   **Authorized Redirect URI:**
    *   This read-only field displays the redirect URI you must use when creating your OAuth client in the Zoho API Console. It usually looks like: `YOUR_WP_ADMIN_URL/admin.php?page=wczb2b-zoho-settings&action=wczb2b_oauth_callback`

*   **Authorization Status & Button:**
    *   Displays the current authorization status (Not Authorized, Token Expired, Authorized).
    *   If Client ID, Secret, and Domain are configured, an "Authorize with Zoho" button will appear. Clicking this initiates the OAuth 2.0 flow. Upon successful authorization, access and refresh tokens are stored.

*   **Synchronization Settings:**
    *   **Sync B2B Users/Contacts:**
        *   If checked, approved B2B applications/users will be synced to Zoho CRM (typically as Accounts and Contacts).
        *   *Option Name:* `wc_zoho_b2b_zoho_sync_users`
    *   **Sync B2B Pricing:** (Advanced/Future Feature)
        *   If checked, enables syncing B2B pricing rules with Zoho (e.g., Price Lists in Zoho Inventory/Books).
        *   *Option Name:* `wc_zoho_b2b_zoho_sync_pricing`

---

## Shortcodes

*   **`[wc_zoho_b2b_application_form]`**: Displays the B2B application form on any page or post.
*   **`[wc_zoho_b2b_wishlist]`**: Displays the current user's wishlist content on any page or post.

Remember to save your settings after making changes on each page.
