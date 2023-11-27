define([
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Customer/js/model/customer',
    'RicardoMartins_PagBank/js/model/payment-validation/pagbank-customer-data',
    'RicardoMartins_PagBank/js/view/payment/form/customer-fields',
    'RicardoMartins_PagBank/js/lib/jquery/jquery.mask',
    'mage/translate'
], function ($, Component, customer, pagbankCustomerData, customerFields, _mask, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            code: 'ricardomartins_pagbank_boleto',
            template: 'RicardoMartins_PagBank/payment/boleto',
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
            const self = this;
            let documentField,
                typeMaskDocument;

            this._super();

            //Set document to data object
            this.taxId.subscribe(function (value) {
                value = value.replace(/\D/g, '');

                documentField = $('#' + self.getCode() + '_tax_id');
                typeMaskDocument = value.length <= 11 ? '000.000.000-009' : '00.000.000/0000-00';
                documentField.mask(typeMaskDocument, {clearIfNotMatch: true});

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
            let expiration_days = window.checkoutConfig.payment[this.getCode()].expiration;
            return $t('Your bill expires in %1 day(s).').replace('%1', expiration_days);
        }
    });
});
