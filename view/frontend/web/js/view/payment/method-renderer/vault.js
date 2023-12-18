define([
    'jquery',
    'underscore',
    'mage/translate',
    'Magento_Checkout/js/model/quote',
    'Magento_Vault/js/view/payment/method-renderer/vault',
    'Magento_Payment/js/model/credit-card-validation/credit-card-data',
    'RicardoMartins_PagBank/js/model/payment-validation/pagbank-customer-data',
    'RicardoMartins_PagBank/js/view/payment/form/customer-fields',
    'RicardoMartins_PagBank/js/action/get-installments',
    'RicardoMartins_PagBank/js/action/set-interest',
    'RicardoMartins_PagBank/js/lib/jquery/jquery.mask',
], function (
    $,
    _,
    $t,
    quote,
    VaultComponent,
    creditCardData,
    pagbankCustomerData,
    customerFields,
    getInstallments,
    setInterest,
    _mask,
) {
    'use strict';

    return VaultComponent.extend({
        defaults: {
            code: 'ricardomartins_pagbank_cc_vault',
            template: 'RicardoMartins_PagBank/payment/vault',
            active: false,
            taxId: null,
            creditCardVaultInstallments: null,
            creditCardVaultInstallmentsOptions: null
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super()
                .observe([
                    'active',
                    'taxId',
                    'creditCardVaultInstallments',
                    'creditCardVaultInstallmentsOptions'
                ]);

            return this;
        },

        /** @inheritdoc */
        initialize: function () {
            const self = this;
            let documentField,
                typeMaskDocument;

            this._super();

            //default installments options
            self.getInstallments(self.getCardBin());

            //Set document to data object and field mask
            this.taxId.subscribe(function (value) {
                value = value.replace(/\D/g, '');

                documentField = $('#' + self.getCode() + '_tax_id');
                typeMaskDocument = value.length <= 11 ? '000.000.000-009' : '00.000.000/0000-00';
                documentField.mask(typeMaskDocument, {clearIfNotMatch: true});

                pagbankCustomerData.taxId = value;
            });

            self.active.subscribe((value) => {
                if (value === true) {
                    self.getListInstallments(self.getCardBin());
                }
            });

            quote.totals.subscribe(function (value) {
                if (self.isActive() === true) {
                    self.getInstallments(self.getCardBin());
                }
            });

            //Set installments to credit card data object
            self.creditCardVaultInstallments.subscribe(function (value) {
                self.setInterest(value);
                creditCardData.creditCardVaultInstallments = value;
            });

        },

        /**
         * @return {Boolean}
         */
        selectPaymentMethod: function () {
            this.creditCardVaultInstallments(null);
            return this._super();
        },

        /**
         * Is Active
         * @returns {Boolean}
         */
        isActive() {
            return this.getId() === this.isChecked();
        },

        /**
         * Get code
         * @returns {String}
         */
        getCode: function() {
            return this.code;
        },

        /**
         * Get token
         * @returns {String}
         */
        getToken: function () {
            return this.publicHash;
        },

        /**
         * Get card bin
         * @returns {string}
         */
        getCardBin() {
            return this.details['cc_bin'];
        },

        /**
         * Get last 4 digits of card
         * @returns {String}
         */
        getMaskedCard: function () {
            return this.details['cc_last4'];
        },

        /**
         * Get expiration date
         * @returns {String}
         */
        getExpirationDate: function () {
            return this.details['cc_exp_month'] + '/' + this.details['cc_exp_year'];
        },

        /**
         * Get card type
         * @returns {String}
         */
        getCardType: function () {
            return this.details['cc_type'];
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
                    'cc_installments': this.creditCardVaultInstallments(),
                    'public_hash': this.getToken()
                }
            };
        },

        /**
         * Request tax id at checkout
         * @returns {boolean}
         */
        requestTaxIdAtCheckout: function () {
            return customerFields().requestTaxIdAtCheckout();
        },

        /**
         * Get list of available instalments options for select
         * @returns {{label, value: null}|*}
         */
        getOptionsVaultInstallments() {
            let self = this;

            if (_.isEmpty(self.creditCardVaultInstallmentsOptions()) || _.isUndefined(self.creditCardVaultInstallmentsOptions())) {
                return {
                    'value': null,
                    'label': $t('Enter the credit card number...')
                };
            }

            return _.map(self.creditCardVaultInstallmentsOptions(), function (option) {
                return {
                    'value': option.value,
                    'label': option.label
                };
            });
        },

        /**
         * Get list of available instalments values
         * @returns {Object}
         */
        getInstallments: function (creditCardBin) {
            let self = this,
                deferred = $.Deferred(),
                quoteId = quote.getQuoteId();

            if (!creditCardBin) {
                return null;
            }

            getInstallments(quoteId, creditCardBin).then(function (response) {
                self.creditCardVaultInstallmentsOptions(response);
                deferred.resolve(response);
            });
        },

        /**
         * Set interest
         * @param installment
         */
        setInterest(installment) {
            let self = this,
                quoteId = quote.getQuoteId(),
                creditCardBin = self.getCardBin();

            if (!installment || !creditCardBin) {
                return;
            }

            setInterest(quoteId, installment, creditCardBin);
        },

        /**
         * Get payment icons
         * @param {String} type
         * @returns {Boolean}
         */
        getIcons(type) {
            return window.checkoutConfig.payment[this.getCode()].icons.hasOwnProperty(type) ?
                window.checkoutConfig.payment[this.getCode()].icons[type]
                : false;
        },
    });
});
