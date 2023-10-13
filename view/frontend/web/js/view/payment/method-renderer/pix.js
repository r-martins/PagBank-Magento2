define([
    'Magento_Checkout/js/view/payment/default',
    'Magento_Customer/js/model/customer',
    'RicardoMartins_PagBank/js/model/payment-validation/pagbank-customer-data',
    'RicardoMartins_PagBank/js/view/payment/form/customer-fields',
    'mage/translate'
], function (Component, customer, pagbankCustomerData, customerFields, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            code: 'ricardomartins_pagbank_pix',
            template: 'RicardoMartins_PagBank/payment/pix',
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
            return this.getCode() === this.isChecked();
        },

        /**
         * Request tax id at checkout
         * @returns {boolean}
         */
        requestTaxIdAtCheckout: function () {
            return customerFields().requestTaxIdAtCheckout();
        },

        /**
         * Get expiration message
         * @returns {string}
         */
        getExpirationMessage: function () {
            let expiration = null,
                expiration_minutes = window.checkoutConfig.payment[this.getCode()].expiration;

            if (expiration_minutes < 60) {
                expiration = $t('%1 minutes').replace('%1', expiration_minutes);
            }
            if (expiration_minutes === 60) {
                expiration = $t('%1 hour').replace('%1', 1);
            }
            if (expiration_minutes > 60) {
                expiration = $t('%1 hours').replace('%1', expiration_minutes / 60);
            }

            return $t('You will have %1 to pay with your Pix code.').replace('%1', expiration);
        }
    });
});
