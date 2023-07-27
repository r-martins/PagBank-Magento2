define([
        'underscore',
        'jquery',
        'Magento_Checkout/js/action/get-totals',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Customer/js/model/customer',
        'mage/url',
        'Magento_Checkout/js/model/error-processor'
    ], function (
        _,
        $,
        getTotalsAction,
        quote,
        totals,
        urlBuilder,
        customer,
        urlFormatter,
        errorProcessor
    ) {
        'use strict';

        return function (cartId, installment, creditCardBin) {
            let requestUrl,
                payload,
                deferred = $.Deferred();

            requestUrl = urlBuilder.createUrl('/carts/pagbank/set-installment-interest', {});

            payload = {
                'cartId': cartId,
                'installment': installment,
                'creditCardBin': creditCardBin
            }

            $.ajax({
                url: urlFormatter.build(requestUrl),
                global: true,
                async: true,
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(payload)
            }).done(function (response) {
                console.info('Interest calculated successfully.');
                getTotalsAction([], deferred);
            }).fail(function (response) {
                console.error(response);
                errorProcessor.process(response);
            });

            return deferred.promise();
        };
    }
);
