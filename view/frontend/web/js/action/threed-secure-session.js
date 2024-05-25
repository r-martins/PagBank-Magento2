/* @api */
define([
    'jquery',
    'underscore',
    'mage/translate',
    'mage/url',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Customer/js/model/customer'
], function (
    $,
    _,
    $t,
    url,
    urlBuilder,
    errorProcessor,
    customer
) {
    'use strict';

    return function (storeId) {
        let requestUrl,
            deferred = $.Deferred();

        requestUrl = urlBuilder.createUrl('/carts/pagbank/threed-secure-session', {
            storeId: storeId
        });

        if (!customer.isLoggedIn()) {
            requestUrl = urlBuilder.createUrl('/guest-carts/pagbank/threed-secure-session', {});
        }

        $.ajax({
            url: url.build(requestUrl),
            global: false,
            contentType: 'application/json',
            type: 'GET',
            async: true
        }).done(function (response) {
            if (!response) {
                deferred.reject('There was an error generating the 3DS session. Please try again.');
                return;
            }
            console.info('3DS session created successfully.');
            deferred.resolve(response);
        }).fail(function (response) {
            console.error(response);
            deferred.reject('There was an error generating the 3DS session. Please try again.');
            errorProcessor.process(response);
        });

        return deferred.promise();
    }
});
