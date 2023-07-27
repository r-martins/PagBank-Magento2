define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (Component, rendererList) {
        'use strict';

        rendererList.push({
            type: 'ricardomartins_pagbank_cc',
            component: 'RicardoMartins_PagBank/js/view/payment/method-renderer/cc-form'
        });

        rendererList.push({
            type: 'ricardomartins_pagbank_boleto',
            component: 'RicardoMartins_PagBank/js/view/payment/method-renderer/boleto'
        });

        rendererList.push({
            type: 'ricardomartins_pagbank_pix',
            component: 'RicardoMartins_PagBank/js/view/payment/method-renderer/pix'
        });

        return Component.extend({});
    }
);
