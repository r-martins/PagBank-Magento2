/* @api */
define([
    'jquery',
    'underscore',
    'mage/translate',
    'mage/url',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/error-processor'
], function ($, _, $t, url, urlBuilder, quote, priceUtils, customer, errorProcessor) {
    'use strict';

    return function (cartId, creditCardBin) {
        let requestUrl,
            payload,
            options,
            deferred = $.Deferred();

        requestUrl = urlBuilder.createUrl('/carts/pagbank/list-installments', {});

        if (!customer.isLoggedIn()) {
            requestUrl = urlBuilder.createUrl('/guest-carts/pagbank/list-installments', {});
        }

        payload = {
            'cartId': cartId,
            'creditCardBin': creditCardBin
        }

        $.ajax({
            url: url.build(requestUrl),
            global: false,
            data: JSON.stringify(payload),
            contentType: 'application/json',
            type: 'POST',
            async: true
        }).done(function (response) {
            options = _.map(response, function (option) {

                let label,
                    infoText,
                    installment,
                    amount;

                installment = priceUtils.formatPrice(option.amount.value / 100, quote.getPriceFormat());
                amount = priceUtils.formatPrice(option.installment_value / 100, quote.getPriceFormat());

                infoText = $t('Total with interest: %1').replace('%1', installment);
                if (option.interest_free) {
                    infoText = $t('Interest free');
                }

                label = $t('%1x of %2 (%3)')
                    .replace('%1', option.installments)
                    .replace('%2', amount)
                    .replace('%3', infoText);

                return {
                    'value': option.installments,
                    'label': label
                };
            });

            deferred.resolve(options);
        }).fail(function (response) {
            console.log(response);
            errorProcessor.process(response);
        });

        return deferred.promise();
    }
});
