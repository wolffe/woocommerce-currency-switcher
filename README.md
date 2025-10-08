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

## Payment Gateway Configuration

### Overview

The plugin provides two ways to control which payment gateways are available to your customers:

1. **Currency-based Gateway Restrictions**: Control which payment gateways are available for each currency
2. **Product-specific Gateway Restrictions**: Control which payment gateways are available for specific products

These features can be used independently or together, with product-specific settings taking precedence over currency-based settings.

### Currency-based Gateway Restrictions

This feature allows you to specify which payment gateways are available for each currency. This is useful when certain payment providers only support specific currencies.

#### How to Set Up Currency-based Gateway Restrictions

1. Go to **WooCommerce > Settings > Currency Switcher**
2. In the "Currency Settings" section, select the currencies you want to support
3. Go to the "Gateway Currency" tab
4. For each payment gateway:
   - Select the currencies in which this gateway should be available
   - Leave currencies unselected to disable the gateway for those currencies

#### Example

Let's say you want to:

- Allow PayPal only for USD and EUR
- Allow Stripe for all currencies
- Allow Global Payments only for EUR and GBP

You would:

1. Select USD and EUR for PayPal
2. Select all currencies for Stripe
3. Select EUR and GBP for Global Payments

### Product-specific Gateway Restrictions

This feature allows you to specify which payment gateways are available for specific products. This is useful when:

- Certain products can only be purchased through specific payment methods
- You want to offer different payment options for different types of products
- You need to restrict payment methods based on product categories or types

#### How to Set Up Product-specific Gateway Restrictions

1. Edit any product in your store
2. Go to the "Payment Gateways" tab
3. Select the payment gateways that should be available for this product
4. Leave all gateways unselected to allow all gateways for this product

#### Example

Let's say you have:

- Product A: Only allow PayPal
- Product B: Only allow Stripe
- Product C: Allow all payment gateways

You would:

1. For Product A: Select only PayPal
2. For Product B: Select only Stripe
3. For Product C: Leave all gateways unselected

### How the Features Work Together

When both features are configured, the plugin follows these rules:

#### 1. Product-specific Settings Take Precedence

- If a product has specific gateway restrictions, only those gateways will be available for that product
- Currency-based settings are ignored for products with specific gateway restrictions

#### 2. Currency-based Settings Apply When No Product-specific Settings Exist

- For products without specific gateway restrictions, the currency-based settings determine which gateways are available
- The current currency must be enabled for the gateway in the currency settings

#### 3. Cart Rules

- When multiple products are in the cart, only the gateways that are allowed for ALL products in the cart will be shown
- This ensures consistency in payment options across the entire order

#### Example Scenarios

##### Scenario 1: Product-specific Settings Override Currency Settings

- Product A: Only allows PayPal
- PayPal is disabled for EUR in currency settings
- When viewing Product A in EUR:
  - PayPal will be available (product-specific setting takes precedence)
  - Other gateways enabled for EUR will not be shown

##### Scenario 2: Multiple Products in Cart

- Product A: Only allows PayPal
- Product B: Only allows Stripe
- When both products are in cart:
  - No payment gateways will be shown (no common gateways)
  - Customer must remove one product to proceed with payment

##### Scenario 3: Mixed Settings

- Product A: No specific gateway restrictions
- Product B: Only allows PayPal
- PayPal is disabled for EUR in currency settings
- When viewing Product A in EUR:
  - PayPal will not be available (currency setting applies)
  - Other gateways enabled for EUR will be shown
- When viewing Product B in EUR:
  - PayPal will be available (product-specific setting takes precedence)
  - Other gateways will not be shown

### Best Practices

#### 1. Plan Your Gateway Strategy

- Consider which payment methods make sense for each product
- Consider which currencies each payment provider supports
- Document your gateway restrictions for future reference

#### 2. Test Different Scenarios

- Test with different currencies
- Test with different product combinations in cart
- Test with both logged-in and guest users

#### 3. Monitor Payment Issues

- Keep track of any payment-related issues
- Adjust gateway restrictions if customers report problems
- Consider adding explanatory notes for restricted payment methods

#### 4. Regular Review

- Periodically review your gateway settings
- Update settings when adding new payment providers
- Update settings when adding new products

### Troubleshooting

If payment gateways are not showing as expected:

#### 1. Check Product Settings

- Verify the product has the correct gateway restrictions
- Check if multiple products in cart have conflicting restrictions

#### 2. Check Currency Settings

- Verify the current currency is enabled for the desired gateway
- Check if the currency is supported by the payment provider

#### 3. Check Cart Contents

- Ensure all products in cart have compatible gateway restrictions
- Try removing products one by one to identify conflicts

#### 4. Check Payment Provider Settings

- Verify the payment provider is properly configured in WooCommerce
- Check if the payment provider supports the current currency
