define([
    'jquery',
    'mage/utils/wrapper',
    'uiRegistry',
    'Magento_Ui/js/lib/view/utils/dom-observer'
], function ($, wrapper, uiRegistry, domObserver) {
    'use strict';

    return function (placeOrderAction) {
        return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, messageContainer) {
            let result,
                errorMessage;

            $.when(
                result = originalAction(paymentData, messageContainer)
            ).fail(
                function () {
                    let showAlternativeMessagesContainer = true;
                    errorMessage = result.responseJSON.message;
                    domObserver.get('.ricardomartins-pagbank-form.credit-card>.payment-method-content>.messages', function () {
                        showAlternativeMessagesContainer = false;
                    });
                    if (errorMessage && showAlternativeMessagesContainer) {
                        uiRegistry.get("checkout.steps.billing-step.payment.payments-list.ricardomartins_pagbank_cc")
                            .set('errorMessage', result.responseJSON.message)
                            .set('showAlternativeMessages', true);
                    }
                }
            );

            return result;
        });
    };
});
