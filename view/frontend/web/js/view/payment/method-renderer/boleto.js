define(
    [
        'Magento_Checkout/js/view/payment/default'
    ],
    function (Component) {
        'use strict';

        return Component.extend({
            defaults: {
                code: 'ricardomartins_pagbank_boleto',
                template: 'RicardoMartins_PagBank/payment/boleto'
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
                // return this.getCode() === this.isChecked();;
                return true;
            },
        });
    }
);
