# WooCommerce Currency Switcher

A WooCommerce plugin that allows you to set fixed prices for products in different currencies, rather than using automatic currency conversion.

## Features

- Set fixed prices for each product in different currencies
- Support for multiple currencies with WooCommerce's native currency symbols
- Light and dark theme options
- Multiple positioning options for the currency switcher
- Shortcode support for manual placement
- Auto-detection of customer's currency based on location
- Support for variable products and variations
- Compatible with WooCommerce HPOS (High-Performance Order Storage)

## Screenshots

![WooCommerce Currency Switcher](https://getbutterfly.com/wp-content/uploads/2025/03/woocommerce-currency-switcher.png)

![WooCommerce Payment Gateways by Currency](https://getbutterfly.com/wp-content/uploads/2025/03/woocommerce-payment-gateways-by-currency.png)

## Installation

1. Upload the plugin files to the `/wp-content/plugins/woo-currency-switcher` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to WooCommerce > Settings > Currency Switcher to configure the plugin

## Configuration

### Basic Settings

#### 1. Currency Selection

- Select the currencies you want to support using the enhanced select dropdown
- The default store currency will be automatically included
- Each selected currency will be available for price setting

#### 2. Theme Selection

- Choose between light and dark themes for the currency switcher
- Light theme is the default option

#### 3. Position Selection

- Choose where the currency switcher appears on the frontend:
  - Center Left
  - Bottom Left
  - Middle Bottom
  - Bottom Right (default)
  - Center Right

#### 4. Auto-detection

- Enable/disable automatic currency detection based on customer location
- Requires MaxMind GeoIP library and proper WooCommerce geolocation settings

### Setting Product Prices

#### 1. Regular Products

- Edit any product
- Scroll to the "Fixed Currency Prices" section
- Enter prices for each supported currency
- Leave empty to use the default currency price

#### 2. Variable Products

- Edit the variable product
- For each variation, you can set specific prices in different currencies
- If no variation-specific price is set, it will fall back to the parent product's price

## Usage

### Fixed Currency Switcher

The currency switcher will appear in the position you selected in the settings. It's fixed on the screen and always visible to customers.

### Shortcode Usage

You can manually place the currency switcher anywhere on your site using the `[wcc_switcher]` shortcode.

This is useful for:

- Placing the switcher in specific locations in your theme
- Adding it to custom templates
- Including it in widgets that support shortcodes

### Auto-detection Requirements

For the auto-detection feature to work, you need to:

1. Install and configure the MaxMind GeoIP library in WooCommerce
2. Set "Default customer location" to "Geolocate" in **WooCommerce** > **Settings** > **General**
3. Enable the auto-detection option in the **Currency Switcher** settings

For more information about MaxMind integration, please read the [WooCommerce documentation on geolocation](https://woocommerce.com/document/maxmind-geolocation-integration/).

## Important Notes

- This plugin uses fixed prices, not automatic currency conversion
- Each currency price is independent and should be set manually
- The default store currency is used as the base currency
- Prices in other currencies are stored as separate meta fields
- The switcher is responsive and works on all devices
- Both light and dark themes are fully styled and accessible

## Demo

See a demo on [Mellon Educate's website](https://donate.melloneducate.com/).
