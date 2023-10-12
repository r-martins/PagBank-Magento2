define([
    'uiComponent',
    'Magento_Customer/js/model/customer'
], function (Component, customer) {
    'use strict';

    return Component.extend({
        defaults: {
            document_from: window.checkoutConfig.payment.ricardomartins_pagbank.document_from
        },

        /**
         * Request tax id at checkout
         * @returns {boolean}
         */
        requestTaxIdAtCheckout: function () {
            return this.document_from === 'payment_form' || (this.document_from === 'taxvat' && !customer.isLoggedIn());
        }
    });
});
