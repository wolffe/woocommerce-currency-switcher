let delay = 600;
let timeout;
var locale_json = window.wc_country_select_params.countries.replace(/"/g, '"');
// console.log( 'locale_json', locale_json );
var states = JSON.parse(locale_json);
let flag = true;

function getParameterByName(name, url = window.location.href) {
    name = name.replace(/[\[\]]/g, '\\$&');
    var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, ' '));
}

function setCookie(cname, cvalue, exdays) {
    const d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    let expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
    let name = cname + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }

        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }

    return false;
}

jQuery(window).on("load", function () {
    if (typeof wc !== 'undefined') {
        // console.log('extensionCartUpdate executed!');
        wc.blocksCheckout.extensionCartUpdate({
            namespace: 'wccs'
        });
    }

    jQuery('.wc-block-components-checkout-place-order-button').click(function (e) {
        let current = jQuery(this);

        if (wccs_checkout.shop_currency != '1') {
            if (flag) {
                e.stopPropagation();
                var url = wccs_checkout.admin_url;
                var data = {
                    action: 'wccs_currency_to_default',
                    nonce: wccs_checkout.nonce,
                };

                jQuery.post(url, data, function (response) {
                    if (response == 'success') {
                        jQuery(document.body).trigger('wc_fragment_refresh');
                        flag = false;
                        current.trigger('click');
                    }
                });
            }
        }
    });

    jQuery(document).on(
        "click",
        "#place_order",
        function (e) {
            let current = jQuery(this);
            if (wccs_checkout.shop_currency != '1') {
                if (flag) {
                    e.preventDefault();
                    var url = wccs_checkout.admin_url;
                    var data = {
                        action: 'wccs_currency_to_default',
                        nonce: wccs_checkout.nonce,
                    };

                    jQuery.post(url, data, function (response) {
                        //response = JSON.parse( response );
                        // debugger;   
                        console.log(response);
                        if (response == 'success') {
                            flag = false;
                            current.trigger('click');
                        }
                    });
                }
            }
        }
    );
});
