define([
    'Magento_Checkout/js/view/payment/default',
    'Magento_Customer/js/model/customer',
    'RicardoMartins_PagBank/js/model/payment-validation/pagbank-customer-data',
    'RicardoMartins_PagBank/js/view/payment/form/customer-fields'
], function (Component, customer, pagbankCustomerData, customerFields) {
    'use strict';

    return Component.extend({
        defaults: {
            code: 'ricardomartins_pagbank_boleto',
            template: 'RicardoMartins_PagBank/payment/boleto',
            document_from: window.checkoutConfig.payment.ricardomartins_pagbank.document_from,
            taxId: null
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super()
                .observe([
                    'taxId'
                ]);

            return this;
        },

        /**
         * Init component
         */
        initialize: function () {
            this._super();

            //Set document to data object
            this.taxId.subscribe(function (value) {
                value = value.replace(/\D/g, '');
                pagbankCustomerData.taxId = value;
            });
        },

        /**
         * Get data
         * @returns {Object}
         */
        getData: function () {
            return {
                'method': this.getCode(),
                'additional_data': {
                    'tax_id': pagbankCustomerData.taxId,
                }
            };
        },

        /**
         * Get code
         * @returns {String}
         */
        getCode: function() {
            return this.code;
        },

        /**
         * Is Active
         * @returns {Boolean}
         */
        isActive() {
            return this.getCode() === this.isChecked();;
        },

        /**
         * Request tax id at checkout
         * @returns {boolean}
         */
        requestTaxIdAtCheckout: function () {
            return customerFields().requestTaxIdAtCheckout();
        }
    });
});
