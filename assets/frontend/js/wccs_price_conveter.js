var currencyRate = currency_convert_rate.currency_rate;

if ( typeof window.wc_price_calculator_params !== 'undefined' && currencyRate ) {
    pricingRules = window.wc_price_calculator_params.pricing_rules;

    let minimum_price = window.wc_price_calculator_params.minimum_price;
    minimum_price = (parseFloat(minimum_price) * currencyRate).toFixed(2);

    // Example modification: Let's say we want to increase the price by 10% for each rule
    pricingRules = pricingRules.map(rule => {
        let modifiedPrice = (parseFloat(rule.price) * currencyRate).toFixed(2); // Increase by 10%
        let modifiedRegularPrice = (parseFloat(rule.regular_price) * currencyRate).toFixed(2); // Increase by 10%
        let modifiedSalePrice = (parseFloat(rule.sale_price) * currencyRate).toFixed(2); // Increase by 10%
        
        // Update price HTML
        let modifiedPriceHtml = `<del><span class="woocommerce-Price-amount amount">
        <bdi><span class="woocommerce-Price-currencySymbol">&#8360;</span>${modifiedRegularPrice}</bdi></span> / Price</del>
        <ins><span class="woocommerce-Price-amount amount">
        <bdi><span class="woocommerce-Price-currencySymbol">&#8360;</span>${modifiedSalePrice}</bdi></span> / Price</ins>`;

        // Return the updated rule
        return {
            ...rule,
            price: modifiedPrice,
            regular_price: modifiedRegularPrice,
            sale_price: modifiedSalePrice,
            price_html: modifiedPriceHtml
        };
    });

    // Store the updated rules back in window object
    window.wc_price_calculator_params = window.wc_price_calculator_params || {};
    window.wc_price_calculator_params.pricing_rules = pricingRules;
    window.wc_price_calculator_params.minimum_price = minimum_price;

    console.log(window.wc_price_calculator_params);
    console.log(window.wc_price_calculator_params.pricing_rules);
}