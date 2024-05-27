define([
        'jquery',
        'mage/translate',
        'Magento_Checkout/js/model/quote',
        'Magento_Ui/js/modal/alert',
        'pagBankSdk'
    ], function (
        $,
        $t,
        quote,
        alert
    ) {
        'use strict';

        /**
         * 3D Secure Action
         * @param context
         * @param three3dSecureSessionId
         */
        return function (
            context,
            three3dSecureSessionId
        ) {
            let deferred = $.Deferred();

            //region 3ds session authentication method
            PagSeguro.setUp({
                session: three3dSecureSessionId,
                env: context.connectEnvironment,
            });
            //endregion

            //region 3ds authentication method
            let totalAmount = parseFloat(quote.totals()['grand_total']) * 100,
                customerEmail = quote.guestEmail ? quote.guestEmail : window.checkoutConfig.customerData.email,
                billingAddress = quote.billingAddress(),
                phone = billingAddress.telephone.replace(/\D/g, ''),
                postcode = billingAddress.postcode.replace(/[^0-9]/g, '');

            const request = {
                data: {
                    customer: {
                        name: billingAddress.firstname + ' ' + billingAddress.lastname,
                        email: customerEmail,
                        phones: [
                            {
                                country: '55',
                                area: phone.substring(0, 2),
                                number: phone.substring(2),
                                type: 'MOBILE'
                            }
                        ]
                    },
                    paymentMethod: {
                        type: 'CREDIT_CARD',
                        installments: context.creditCardInstallments(),
                        card: {
                            number: context.creditCardNumber().replace(/\s/g,''),
                            expMonth: context.creditCardExpMonth(),
                            expYear: context.creditCardExpYear(),
                            holder: {
                                name: context.creditCardOwner()
                            }
                        }
                    },
                    amount: {
                        value: totalAmount,
                        currency: 'BRL'
                    },
                    billingAddress: {
                        street: billingAddress.street[0],
                        number: billingAddress.street[1],
                        complement: billingAddress.street[2] ? billingAddress.street[2] : null,
                        regionCode: billingAddress.regionCode,
                        country: 'BRA',
                        city: billingAddress.city,
                        postalCode: postcode
                    },
                    dataOnly: false
                }
            };

            PagSeguro.authenticate3DS(request).then(result => {
                let resultStatus = handle3DSecureStatus(result, context);
                deferred.resolve(resultStatus);
            }).catch((err) => {
                if (err instanceof PagSeguro.PagSeguroError) {
                    deferred.resolve(false);
                } else {
                    deferred.reject(err);
                }
            });
            //endregion

            return deferred.promise();
        };

        /**
         * Handle 3DSecure Status
         *
         * @param result
         * @param context
         * @returns {boolean}
         */
        function handle3DSecureStatus(result, context) {
            switch (result.status) {
                case 'CHANGE_PAYMENT_METHOD':
                    // The user must change the payment method used
                    alert({
                        content: $t('Payment denied by PagBank. Choose another payment method or card.')
                    });
                    return false;
                case 'AUTH_FLOW_COMPLETED':
                    // O processo de autenticação foi realizado com sucesso, dessa forma foi gerado um id do 3DS que
                    // poderá ter o resultado igual a Autenticado ou Não Autenticado.
                    if (result.authenticationStatus === 'AUTHENTICATED') {
                        //O cliente foi autenticado com sucesso, dessa forma o pagamento foi autorizado.
                        context.creditCardThreeDSecureId(result.id);
                        console.debug('PagBank: 3DS Autenticado ou Sem desafio');
                        return true;
                    }
                    alert({
                        content: $t('3D authentication failed. Try again.')
                    });
                    return false;
                case 'AUTH_NOT_SUPPORTED':
                    // A autenticação 3DS não ocorreu, isso pode ter ocorrido por falhas na comunicação com emissor
                    // ou bandeira, ou algum controle que não possibilitou a geração do 3DS id, essa transação não terá
                    // um retorno de status de autenticação e seguirá como uma transação sem 3DS.
                    // O cliente pode seguir adiante sem 3Ds (exceto débito)

                    if (context.allowContinueWithout3DS) {
                        console.debug('PagBank: 3DS não suportado pelo cartão. Continuando sem 3DS.');
                        return true;
                    }

                    alert({
                        content: $t('Your card does not support 3D authentication. Choose another payment method or card.')
                    });
                    return false;
                case 'REQUIRE_CHALLENGE':
                    // É um status intermediário que é retornando em casos que o banco emissor solicita desafios,
                    // é importante para identificar que o desafio deve ser exibido.
                    console.debug('PagBank: REQUIRE_CHALLENGE - O desafio está sendo exibido pelo banco.');
                    break;
            }
        }
    }
);
