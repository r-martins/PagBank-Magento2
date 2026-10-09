define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    'Magento_Checkout/js/model/quote',
    'RicardoMartins_PagBank/js/model/payment-validation/pagbank-customer-data'
], function ($, $t, alert, quote, pagbankCustomerData) {
    'use strict';

    function cfg() {
        return window.checkoutConfig.payment.ricardomartins_pagbank;
    }

    function customerEmail() {
        var address = quote.billingAddress() || {},
            customer = (window.checkoutConfig && window.checkoutConfig.customerData) || {},
            guest = quote.guestEmail;

        if (typeof guest === 'function') {
            guest = guest();
        }

        return guest || customer.email || address.email || '';
    }

    function checkoutPayload() {
        var address = quote.billingAddress() || {},
            street = address.street || [],
            totals = quote.totals() || {};

        return {
            customerName: ((address.firstname || '') + ' ' + (address.lastname || '')).trim(),
            email: customerEmail(),
            tax_id: pagbankCustomerData.taxId || '',
            phone: address.telephone || '',
            street: street[0] || '',
            number: street[1] || 'S/N',
            city: address.city || '',
            regionCode: address.regionCode || '',
            postalCode: address.postcode || '',
            totalAmount: totals.grand_total || 0
        };
    }

    function postJson(url, payload) {
        return fetch(url, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            credentials: 'same-origin',
            body: JSON.stringify(payload || {})
        }).then(function (resp) {
            return resp.json().catch(function () {
                return {};
            }).then(function (json) {
                if (!resp.ok || json.success === false) {
                    throw new Error(json.message || $t('Vindi 3DS request failed.'));
                }
                return json.data || json;
            });
        });
    }

    function runDdc(setup) {
        return new Promise(function (resolve) {
            var mpiOrigin = cfg().vindi_mpi_origin || 'https://mpi.vindi.com.br',
                iframe = document.getElementById('rm-pagbank-vindi-ddc-iframe'),
                form,
                input;

            if (iframe) {
                iframe.remove();
            }
            iframe = document.createElement('iframe');
            iframe.id = 'rm-pagbank-vindi-ddc-iframe';
            iframe.name = 'rm-pagbank-vindi-ddc-iframe';
            iframe.style.display = 'none';
            document.body.appendChild(iframe);

            form = document.createElement('form');
            form.method = 'POST';
            form.target = 'rm-pagbank-vindi-ddc-iframe';
            form.action = setup.device_data_collection_url || (mpiOrigin + '/V1/Cruise/Collect');
            form.style.display = 'none';
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'JWT';
            input.value = setup.access_token || '';
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
            form.remove();
            setTimeout(resolve, 3000);
        });
    }

    function runChallenge(enroll, company) {
        return new Promise(function (resolve, reject) {
            var onMsg = function (event) {
                var data = event.data || {},
                    wrap;

                if (!data || data.source !== 'rm-pagbank-vindi-3ds') {
                    return;
                }
                window.removeEventListener('message', onMsg);
                wrap = document.getElementById('rm-pagbank-vindi-challenge-wrap');
                if (wrap) {
                    wrap.remove();
                }
                postJson(cfg().vindi_3ds_validate_endpoint, {
                    authentication_transaction_id: data.TransactionId || enroll.authentication_transaction_id,
                    card_type: company || 'visa'
                }).then(function (validateData) {
                    resolve((validateData && validateData.validate) || validateData || enroll);
                }).catch(reject);
            },
                wrap = document.getElementById('rm-pagbank-vindi-challenge-wrap'),
                iframe,
                form,
                jwt;

            window.addEventListener('message', onMsg);
            if (wrap) {
                wrap.remove();
            }
            wrap = document.createElement('div');
            wrap.id = 'rm-pagbank-vindi-challenge-wrap';
            wrap.style.cssText = 'position:fixed;z-index:99999;inset:0;background:rgba(0,0,0,.45);display:flex;align-items:center;justify-content:center;';
            iframe = document.createElement('iframe');
            iframe.id = 'rm-pagbank-vindi-stepup-iframe';
            iframe.name = 'rm-pagbank-vindi-stepup-iframe';
            iframe.style.cssText = 'width:420px;height:520px;max-width:95vw;max-height:90vh;background:#fff;border:0;border-radius:4px;';
            wrap.appendChild(iframe);
            document.body.appendChild(wrap);

            form = document.createElement('form');
            form.method = 'POST';
            form.target = 'rm-pagbank-vindi-stepup-iframe';
            form.action = enroll.step_up_url;
            form.style.display = 'none';
            jwt = document.createElement('input');
            jwt.type = 'hidden';
            jwt.name = 'JWT';
            jwt.value = enroll.step_up_token || enroll.access_token || '';
            form.appendChild(jwt);
            document.body.appendChild(form);
            form.submit();
            form.remove();
        });
    }

    return function (component) {
        var deferred = $.Deferred(),
            checkout = checkoutPayload(),
            company = component.paymentCompanyCode() || window.ps_vindi_payment_company || 'mastercard',
            installments = parseInt(component.creditCardInstallments(), 10) || 1,
            amountCents = Math.round(parseFloat(checkout.totalAmount || 0) * 100);

        postJson(cfg().vindi_payment_profile_endpoint, {
            gateway_token: component.creditCardNumberEncrypted(),
            payment_company_code: company,
            checkout: checkout
        }).then(function (profileData) {
            component.paymentProfileId(profileData.payment_profile_id);
            return postJson(cfg().vindi_3ds_setup_endpoint, {
                payment_profile_id: profileData.payment_profile_id
            });
        }).then(function (setupData) {
            var setup = setupData.setup || setupData;

            return runDdc(setup).then(function () {
                return setup;
            });
        }).then(function (setup) {
            return postJson(cfg().vindi_3ds_enroll_endpoint, {
                session_id: setup.session_id,
                amount: amountCents,
                installments: installments,
                device_info: {
                    screen_height: window.screen.height,
                    screen_width: window.screen.width,
                    color_depth: window.screen.colorDepth,
                    timezone_offset: new Date().getTimezoneOffset(),
                    language: navigator.language || 'pt-BR',
                    java_enabled: false,
                    user_agent: navigator.userAgent
                },
                checkout: checkout
            });
        }).then(function (enrollData) {
            var enroll = enrollData.enroll || enrollData;

            if (enroll && enroll.step_up_url &&
                (enroll.status === 'AUTHENTICATION_REQUIRED' || enroll.status === 'PENDING_AUTHENTICATION')) {
                // Keep the profile created before setup. A new public token is not a payment_profile id.
                return runChallenge(enroll, company);
            }

            return enroll;
        }).then(function (authResult) {
            var threeDs = {
                status: (authResult && authResult.status) || 'AUTHENTICATION_SUCCESSFUL',
                liability_shift: !!(authResult && authResult.liability_shift),
                consumer_authentication_information: (authResult && (
                    authResult.consumer_authentication_information || authResult.authentication_information
                )) || {}
            };

            component.cc3dsPayload(JSON.stringify(threeDs));
            component.creditCardThreeDSecureId(
                (authResult && (authResult.authentication_transaction_id || authResult.id)) || 'vindi_3ds'
            );
            deferred.resolve(true);
        }).catch(function (error) {
            alert({
                content: $t('3D Secure authentication failed.') + '\n' + (error && error.message ? error.message : '')
            });
            deferred.resolve(false);
        });

        return deferred.promise();
    };
});
