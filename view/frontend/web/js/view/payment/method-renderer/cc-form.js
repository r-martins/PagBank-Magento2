define([
        'jquery',
        'underscore',
        'ko',
        'pagBankSdk',
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Vault/js/view/payment/vault-enabler',
        'Magento_Checkout/js/model/quote',
        'Magento_Payment/js/model/credit-card-validation/credit-card-data',
        'RicardoMartins_PagBank/js/model/payment-validation/pagbank-customer-data',
        'Magento_Payment/js/model/credit-card-validation/credit-card-number-validator',
        'RicardoMartins_PagBank/js/action/get-installments',
        'RicardoMartins_PagBank/js/action/set-interest',
        'RicardoMartins_PagBank/js/action/encrypt-card',
        'RicardoMartins_PagBank/js/action/threed-secure-action',
        'RicardoMartins_PagBank/js/action/threed-secure-session',
        'RicardoMartins_PagBank/js/view/payment/form/customer-fields',
        'RicardoMartins_PagBank/js/lib/jquery/jquery.mask',
        'Magento_Checkout/js/model/full-screen-loader',
        'mage/translate',
        'uiRegistry',
        'Magento_Ui/js/model/messageList'
    ], function (
        $,
        _,
        ko,
        pagBankSdk,
        Component,
        VaultEnabler,
        quote,
        creditCardData,
        pagbankCustomerData,
        cardNumberValidator,
        getInstallments,
        setInterest,
        encryptCard,
        threeDSecureAction,
        threedSecureSession,
        customerFields,
        _mask,
        fullScreenLoader,
        $t
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                code: 'ricardomartins_pagbank_cc',
                template: 'RicardoMartins_PagBank/payment/cc-form',
                showAlternativeMessages: ko.observable(false),
                errorMessage: ko.observable(''),
                creditCardThreeDSecureId: '',
                creditCardThreeDSecureSession: '',
                creditCardNumberEncrypted: '',
                creditCardBin: '',
                creditCardExpiration: null,
                creditCardInstallments: null,
                creditCardInstallmentsOptions: null,
                creditCardOwner: '',
                taxId: null,
                allowContinueWithout3DS: false,
                connectEnvironment: null,
            },

            validate: function () {
                let $form = $('#' + this.getCode() + '_payment-form');
                return $form.validation() && $form.validation('isValid');
            },

            /** @inheritdoc */
            initObservable: function () {
                this._super()
                    .observe([
                        'creditCardThreeDSecureId',
                        'creditCardThreeDSecureSession',
                        'creditCardNumberEncrypted',
                        'creditCardBin',
                        'creditCardExpiration',
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
                let documentField,
                    creditCardNumberField,
                    expirationField,
                    cvvField,
                    typeMaskDocument,
                    typeMaskCreditCard;

                this._super();

                self.vaultEnabler = new VaultEnabler();
                self.vaultEnabler.setPaymentCode(self.getVaultCode());

                //default installments options
                self.setCardBin('555566');
                self.getInstallments();

                //3D Secure Config
                self.allowContinueWithout3DS = window.checkoutConfig.payment[self.getCode()].ccThreeDSecureAllowContinue;
                self.connectEnvironment = window.checkoutConfig.payment[self.getCode()].environment;

                //Process credit card number functions
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
                        value = value.replace(/ /g, "");
                        creditCardData.creditCardNumber = value;
                        self.creditCardType(result.card.type);
                        self.setCardBin(value);
                        self.getInstallments();
                    }
                });

                this.selectedCardType.subscribe(function (value) {
                    creditCardData.selectedCardType = value;

                    creditCardNumberField = $('#' + self.getCode() + '_cc_number');

                    typeMaskCreditCard = '0000 0000 0000 0000';
                    if (value === 'DN') {
                        typeMaskCreditCard = '0000 000000 0000';
                    }
                    if (value === 'AE') {
                        typeMaskCreditCard = '0000 000000 00000';
                    }

                    creditCardNumberField.mask(typeMaskCreditCard);
                });

                quote.totals.subscribe(function (value) {
                    if (self.creditCardBin() && self.isActive() === true) {
                        self.getInstallments();
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

                //Set expiration date to credit card data object and field mask
                this.creditCardExpiration.subscribe(function (value) {
                    expirationField = $('#' + self.getCode() + '_cc_expiration');
                    expirationField.mask('00/00');

                    self.setExpirationDate(value);
                    creditCardData.creditCardExpMonth = self.creditCardExpMonth();
                    creditCardData.creditCardExpYear = self.creditCardExpYear();
                });

                //Set document to data object and field mask
                this.taxId.subscribe(function (value) {
                    value = value.replace(/\D/g, '');

                    documentField = $('#' + self.getCode() + '_tax_id');
                    typeMaskDocument = value.length <= 11 ? '000.000.000-009' : '00.000.000/0000-00';
                    documentField.mask(typeMaskDocument, {clearIfNotMatch: true});

                    pagbankCustomerData.taxId = value;
                });

                //Set cvv to credit card mask
                this.creditCardVerificationNumber.subscribe(function (value) {
                    cvvField = $('#' + self.getCode() + '_cc_cid');
                    cvvField.mask('0009');
                    creditCardData.creditCardVerificationNumber = value;
                });
            },

            beforePlaceOrder(data, event) {
                let self = this,
                    resultSecure,
                    resultToken;

                if (!this.validate()) {
                    return false;
                }

                if (event) {
                    event.preventDefault();
                }

                fullScreenLoader.startLoader();

                resultToken = this.tokenizeCard();

                if (resultToken) {
                    this.secureAction()
                        .then(function (response) {
                            if (response) {
                                self.placeOrder('parent');
                            }
                        });
                }
            },

            /**
             * @return {Boolean}
             */
            selectPaymentMethod: function () {
                this.creditCardInstallments(null);
                return this._super();
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

            /**
             * Get data
             * @returns {Object}
             */
            getData: function () {
                let data = {
                    'method': this.getCode(),
                    'additional_data': {
                        'cc_number_encrypted': this.creditCardNumberEncrypted(),
                        'cc_owner': this.creditCardOwner(),
                        'cc_type': this.creditCardType(),
                        'cc_last_4': this.getLast4Numbers(),
                        'cc_exp_month': this.creditCardExpMonth(),
                        'cc_exp_year': this.creditCardExpYear(),
                        'cc_installments': this.creditCardInstallments(),
                        'tax_id': pagbankCustomerData.taxId,
                        'threed_secure_id': this.creditCardThreeDSecureId(),
                    }
                };

                data['additional_data'] = _.extend(data['additional_data'], this.additionalData);
                this.vaultEnabler.visitAdditionalData(data);

                return data;
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

                if (_.isEmpty(self.creditCardInstallmentsOptions()) || _.isUndefined(self.creditCardInstallmentsOptions())) {
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

            setCardBin: function (creditCardNumber) {
                let self = this,
                    creditCardBin;

                creditCardNumber = creditCardNumber.replace(/ /g, "");
                creditCardBin = creditCardNumber.slice(0,6);

                self.creditCardBin(creditCardBin);
            },

            /**
             * Get list of available instalments values
             * @returns {Object}
             */
            getInstallments: function () {
                let self = this,
                    deferred = $.Deferred(),
                    quoteId = quote.getQuoteId(),
                    creditCardBin = self.creditCardBin();

                if (!self.creditCardBin()) {
                    return null;
                }

                getInstallments(quoteId, creditCardBin).then(function (response) {
                    self.creditCardInstallmentsOptions(response);
                    deferred.resolve(response);
                });
            },

            setInterest(installment) {
                let self = this,
                    quoteId = quote.getQuoteId(),
                    creditCardBin = self.creditCardBin();

                if (!installment || !creditCardBin) {
                    return;
                }

                setInterest(quoteId, installment, creditCardBin);
            },

            /**
             * 3D Secure action
             */
            secureAction: async function() {
                let self = this,
                    deferred = $.Deferred();

                if (!window.checkoutConfig.payment[this.getCode()].ccThreeDSecure) {
                    deferred.resolve(true);
                }

                threedSecureSession(quote.getStoreCode())
                    .then(function (three3dSecureSessionId) {
                        threeDSecureAction(
                            self,
                            three3dSecureSessionId
                        ).then(function (response) {
                            fullScreenLoader.stopLoader();
                            deferred.resolve(response);
                        }).catch(function (error) {
                            fullScreenLoader.stopLoader();
                        });
                    });

                return deferred.promise();
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
                    self.creditCardNumber().replace(/ /g, ""),
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
             * Set expiration date
             * @param value
             */
            setExpirationDate(value) {
                let self = this,
                    date = value.split('/');

                self.creditCardExpMonth(date[0]);
                self.creditCardExpYear('20' + date[1]);
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
            },

            /**
             * Get vault code
             * @returns {Boolean}
             */
            getVaultCode() {
                return window.checkoutConfig.payment[this.getCode()].ccVaultCode;
            },

            /**
             * Is vault enabled
             * @returns {Boolean}
             */
            isVaultEnabled: function () {
                return this.vaultEnabler.isVaultEnabled();
            }
        });
    }
);
