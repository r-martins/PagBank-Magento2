define([
    'jquery',
    'mage/utils/wrapper',
    'uiRegistry',
    'Magento_Ui/js/lib/view/utils/dom-observer',
    'Magento_Checkout/js/model/full-screen-loader'
], function ($, wrapper, uiRegistry, domObserver, fullScreenLoader) {
    'use strict';

    return function (placeOrderAction) {
        return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, messageContainer) {
            let result = originalAction(paymentData, messageContainer);

            result.fail(function (xhr) {
                let showAlternativeMessagesContainer = true;
                let errorMessage = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Ocorreu um erro no processamento do pagamento.';
                domObserver.get('.ricardomartins-pagbank-form.credit-card>.payment-method-content>.messages', function () {
                    showAlternativeMessagesContainer = false;
                });

                if (errorMessage && showAlternativeMessagesContainer) {
                    uiRegistry.get("checkout.steps.billing-step.payment.payments-list.ricardomartins_pagbank_cc")
                        .set('errorMessage', errorMessage)
                        .set('showAlternativeMessages', true);
                }

                fullScreenLoader.stopLoader();
            });
            return result;
        });
    };
});
