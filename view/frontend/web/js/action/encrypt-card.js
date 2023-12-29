define([
        'jquery',
        'mage/translate',
        'Magento_Ui/js/modal/alert',
        'pagBankSdk'
    ], function (
        $,
        $t,
        alert
    ) {
        'use strict';

        return function (
            holder,
            number,
            expMonth,
            expYear,
            securityCode
        ) {
            let card,
                cardEncrypted,
                cardData;

            // Adjust month to PagSeguro format
            if (expMonth < 10) {
                expMonth = '0' + expMonth;
            }

            cardData = {
                publicKey: window.checkoutConfig.payment.ricardomartins_pagbank.public_key,
                holder: holder,
                number: number,
                expMonth: expMonth,
                expYear: expYear,
                securityCode: securityCode
            };

            try {
                card = PagSeguro.encryptCard(cardData);
            } catch (e) {
                alert({
                    content: $t('Error encrypting the card.\nCheck if the data entered is correct.')
                });
                return false;
            }

            if (card.hasErrors) {
                console.error(card.errors);
                let error_codes = [
                    {code: 'INVALID_NUMBER', message: 'Invalid card number.'},
                    {code: 'INVALID_EXPIRATION_YEAR', message: 'Invalid expiration year.'},
                    {code: 'INVALID_PUBLIC_KEY', message: 'Invalid Public Key.'},
                    {code: 'INVALID_HOLDER', message: 'Invalid cardholder name.'},
                    {
                        code: 'INVALID_SECURITY_CODE',
                        message: 'Invalid CVV. You must pass a value with 3, 4 or more digits.'
                    },
                    {
                        code: 'INVALID_EXPIRATION_MONTH',
                        message: 'Incorrect expiration month. Pass a value between 1 and 12.'
                    },
                ]
                //extract error message
                let error = '';
                for (let i = 0; i < card.errors.length; i++) {
                    //loop through error codes to find the message
                    for (let j = 0; j < error_codes.length; j++) {
                        if (error_codes[j].code === card.errors[i].code) {
                            error += error_codes[j].message + '\n';
                            break;
                        }
                    }
                }
                console.log(error);
                alert({
                    content: $t('Error encrypting card.\n') + $t(error)
                });
                return false;
            }

            cardEncrypted = card.encryptedCard;
            if (!cardEncrypted) {
                return false;
            }

            return cardEncrypted;
        };
    }
);
