# Installation Guide - WooCommerce Zoho B2B Manager

This document provides instructions on how to install the WooCommerce Zoho B2B Manager plugin.

## Requirements

Before you begin, please ensure you have the following:

*   WordPress version 6.0 or higher.
*   WooCommerce version 9.0 or higher, installed and activated.
*   PHP version 7.4 or higher.
*   (Recommended) The "WooCommerce Zoho Integration" plugin by axelbriones (github.com/axelbriones/woocommerce-zoho-integration) if you plan to use its Zoho API connection.

## Installation Steps

1.  **Download the Plugin:**
    *   If you have a ZIP file of the plugin, proceed to step 2.
    *   If you are installing from source (e.g., from a Git repository), ensure you have the complete plugin folder.

2.  **Upload to WordPress:**
    *   Navigate to your WordPress admin dashboard.
    *   Go to **Plugins > Add New**.
    *   Click the **Upload Plugin** button at the top of the page.
    *   Click **Choose File** and select the `woocommerce-zoho-b2b-manager.zip` file (or the ZIP file you have).
    *   Click **Install Now**.

3.  **Activate the Plugin:**
    *   After the installation is complete, click the **Activate Plugin** button.

4.  **Verify WooCommerce:**
    *   The plugin will check if WooCommerce is active. If not, you will see a notice prompting you to install/activate WooCommerce.

## Initial Configuration

Once activated, you will typically find the plugin's settings page under **WooCommerce > Zoho B2B Settings** (the exact menu location might be subject to final implementation).

Refer to the [Configuration Guide (configuration.md)](configuration.md) for details on setting up the plugin.

## Troubleshooting

*   **Plugin does not activate:** Ensure all requirements (PHP version, WordPress version, WooCommerce active) are met. Check for any error messages displayed during activation.
*   **Settings page not visible:** Double-check the activation status. Clear any caching if you are using caching plugins.

For further assistance, please refer to the plugin's support channels or documentation.
