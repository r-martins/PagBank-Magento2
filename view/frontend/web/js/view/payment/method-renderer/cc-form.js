define([
        'jquery',
        'underscore',
        'pagBankSdk',
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Checkout/js/model/quote',
        'Magento_Payment/js/model/credit-card-validation/credit-card-data',
        'RicardoMartins_PagBank/js/model/payment-validation/pagbank-customer-data',
        'Magento_Payment/js/model/credit-card-validation/credit-card-number-validator',
        'RicardoMartins_PagBank/js/action/get-installments',
        'RicardoMartins_PagBank/js/action/set-interest',
        'RicardoMartins_PagBank/js/action/encrypt-card',
        'RicardoMartins_PagBank/js/view/payment/form/customer-fields',
        'Magento_Checkout/js/model/full-screen-loader',
        'mage/translate'
    ], function (
        $,
        _,
        pagBankSdk,
        Component,
        quote,
        creditCardData,
        pagbankCustomerData,
        cardNumberValidator,
        getInstallments,
        setInterest,
        encryptCard,
        customerFields,
        fullScreenLoader,
        $t
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                code: 'ricardomartins_pagbank_cc',
                template: 'RicardoMartins_PagBank/payment/cc-form',
                creditCardNumberEncrypted: '',
                creditCardInstallments: null,
                creditCardInstallmentsOptions: null,
                creditCardOwner: '',
                taxId: null
            },

            validate: function () {
                let $form = $('#' + this.getCode() + '_payment-form');
                return $form.validation() && $form.validation('isValid');
            },

            /** @inheritdoc */
            initObservable: function () {
                this._super()
                    .observe([
                        'creditCardNumberEncrypted',
                        'creditCardInstallments',
                        'creditCardInstallmentsOptions',
                        'creditCardOwner',
                        'taxId'
                    ]);

                return this;
            },

            /**
             * Init component
             */
            initialize: function () {
                const self = this;

                this._super();

                //Set credit card number to credit card data object
                this.creditCardNumber.subscribe(function (value) {
                    let result;

                    self.selectedCardType(null);

                    if (value === '' || value === null) {
                        return false;
                    }
                    result = cardNumberValidator(value);

                    if (!result.isPotentiallyValid && !result.isValid) {
                        return false;
                    }

                    if (result.card !== null) {
                        self.selectedCardType(result.card.type);
                        creditCardData.creditCard = result.card;
                    }

                    if (result.isValid) {
                        creditCardData.creditCardNumber = value;
                        self.creditCardType(result.card.type);
                        self.getInstallments(value);
                    }
                });

                quote.totals.subscribe(function (value) {
                    if (self.creditCardNumber()) {
                        self.getInstallments(self.creditCardNumber());
                    }
                });

                //Set installments to credit card data object
                this.creditCardInstallments.subscribe(function (value) {
                    self.setInterest(value);
                    creditCardData.creditCardInstallments = value;
                });

                //Set owner to credit card data object
                this.creditCardOwner.subscribe(function (value) {
                    creditCardData.creditCardOwner = value;
                });

                //Set document to data object
                this.taxId.subscribe(function (value) {
                    value = value.replace(/\D/g, '');
                    pagbankCustomerData.taxId = value;
                });
            },

            placeOrder: function (data, event) {
                let result;

                if (!this.validate()) {
                    return false;
                }

                fullScreenLoader.startLoader();
                result = this.tokenizeCard();
                fullScreenLoader.stopLoader();

                if(result) {
                    this._super(data, event);
                }
            },

            /**
             * Get data
             * @returns {Object}
             */
            getData: function () {
                return {
                    'method': this.getCode(),
                    'additional_data': {
                        'cc_owner': this.creditCardOwner(),
                        'cc_type': this.creditCardType(),
                        'cc_last_4': this.getLast4Numbers(),
                        'cc_exp_month': this.creditCardExpMonth(),
                        'cc_exp_year': this.creditCardExpYear(),
                        'cc_installments': this.creditCardInstallments(),
                        'tax_id': pagbankCustomerData.taxId
                    }
                }
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
             * Get list of available instalments options for select
             * @returns {{label, value: null}|*}
             */
            getOptionsInstallments() {
                let self = this;

                if (!self.creditCardInstallmentsOptions()) {
                    return {
                        'value': null,
                        'label': $t('Enter the credit card number...')
                    };
                }

                return _.map(self.creditCardInstallmentsOptions(), function (option) {
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
            getInstallments: function (creditCardNumber) {
                let self = this,
                    deferred = $.Deferred(),
                    quoteId = quote.getQuoteId(),
                    creditCardBin = null;

                if (!creditCardNumber) {
                    return null;
                }

                creditCardBin = creditCardNumber.slice(0,6);

                getInstallments(quoteId, creditCardBin).then(function (response) {
                    self.creditCardInstallmentsOptions(response);
                    deferred.resolve(response);
                });
            },

            setInterest(installment) {
                let self = this,
                    quoteId = quote.getQuoteId(),
                    creditCardBin = self.creditCardNumber().slice(0, 6);

                if (!installment) {
                    return;
                }

                setInterest(quoteId, installment, creditCardBin);
            },

            /**
             * Tokenize card
             * @returns {boolean}
             */
            tokenizeCard() {
                let self = this,
                    cardEncrypted;

                cardEncrypted = encryptCard(
                    self.creditCardOwner(),
                    self.creditCardNumber(),
                    self.creditCardExpMonth(),
                    self.creditCardExpYear(),
                    self.creditCardVerificationNumber()
                );

                if (cardEncrypted) {
                    self.creditCardNumberEncrypted(cardEncrypted);
                    return true;
                }
            },

            /**
             * Get last 4 numbers of credit card
             * @returns {*|string}
             */
            getLast4Numbers() {
                let cardNumber = this.creditCardNumber();
                if (!_.isUndefined(cardNumber)) {
                    return cardNumber.slice(-4);
                }

                return "";
            },

            /**
             * Request tax id at checkout
             * @returns {boolean}
             */
            requestTaxIdAtCheckout: function () {
                return customerFields().requestTaxIdAtCheckout();
            }
        });
    }
);
