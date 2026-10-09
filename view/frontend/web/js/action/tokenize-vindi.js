define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/alert'
], function ($, $t, alert) {
    'use strict';

    function detectCompany(ccNumber) {
        var n = String(ccNumber || '').replace(/\D/g, '');

        if (/^(606282|3841)/.test(n)) {
            return 'hipercard';
        }
        if (/^(4011|4312|4389|4514|4576|5041|5067|5090|6277|6362|6363|6504|6505|6507|6509|6516|6550)/.test(n)) {
            return 'elo';
        }
        if (/^3[47]/.test(n)) {
            return 'american_express';
        }
        if (/^3(?:0[0-5]|[68])/.test(n)) {
            return 'diners_club';
        }
        if (/^6(?:011|5)/.test(n)) {
            return 'discover';
        }
        if (/^5[1-5]/.test(n) || /^2(2[2-9]|[3-6]|7[01])/.test(n)) {
            return 'mastercard';
        }
        if (/^4/.test(n)) {
            return 'visa';
        }

        return 'mastercard';
    }

    return function (component) {
        var deferred = $.Deferred(),
            cfg = window.checkoutConfig.payment.ricardomartins_pagbank,
            holder = component.normalizeOwnerNameOnBlur(component.creditCardOwner()),
            number = String(component.creditCardNumber() || '').replace(/\s/g, ''),
            cvc = String(component.creditCardVerificationNumber() || '').replace(/\s/g, ''),
            month = String(component.creditCardExpMonth() || '').replace(/\D/g, ''),
            year = String(component.creditCardExpYear() || '').replace(/\D/g, ''),
            company,
            cardExpiration;

        month = ('0' + month).slice(-2);
        year = year.slice(-2);
        if (!holder || !number || !cvc || !/^(0[1-9]|1[0-2])$/.test(month) || !/^\d{2}$/.test(year)) {
            alert({content: $t('Invalid card expiration date. Use MM/YY.')});
            deferred.resolve(false);
            return deferred.promise();
        }

        company = detectCompany(number);
        cardExpiration = month + '/20' + year;
        if (!cfg.public_key || !cfg.vindi_payment_profiles_url) {
            alert({
                content: $t('Vindi tokenization is unavailable. Reload the page or check the Connect Key.')
            });
            deferred.resolve(false);
            return deferred.promise();
        }

        fetch(cfg.vindi_payment_profiles_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Basic ' + btoa(String(cfg.public_key) + ':')
            },
            body: JSON.stringify({
                holder_name: holder,
                card_expiration: cardExpiration,
                card_number: number,
                card_cvv: cvc,
                payment_method_code: 'credit_card',
                payment_company_code: company
            })
        }).then(function (resp) {
            return resp.json().catch(function () {
                return {};
            }).then(function (body) {
                return {ok: resp.ok, body: body};
            });
        }).then(function (result) {
            var token,
                detail = '';

            if (!result.ok) {
                if (result.body && Array.isArray(result.body.errors)) {
                    detail = result.body.errors.map(function (error) {
                        return (error.parameter ? error.parameter + ': ' : '') + (error.message || '');
                    }).filter(Boolean).join('\n');
                }
                alert({
                    content: $t('Error tokenizing the card.') + (detail ? '\n' + detail : '')
                });
                deferred.resolve(false);
                return;
            }

            token = result.body.payment_profile &&
                (result.body.payment_profile.gateway_token || result.body.gateway_token);
            if (!token) {
                alert({content: $t('Error tokenizing the card (missing token).')});
                deferred.resolve(false);
                return;
            }

            component.creditCardNumberEncrypted(token);
            component.paymentCompanyCode(company);
            window.ps_vindi_payment_company = company;
            deferred.resolve(true);
        }).catch(function () {
            alert({content: $t('Error tokenizing the card. Check the data entered.')});
            deferred.resolve(false);
        });

        return deferred.promise();
    };
});
