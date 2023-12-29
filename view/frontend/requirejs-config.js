var config = {
    paths: {
        'pagBankSdk': [
            'https://assets.pagseguro.com.br/checkout-sdk-js/rc/dist/browser/pagseguro.min',
            'RicardoMartins_PagBank/js/lib/pagseguro'
        ]
    },
    shim: {
        'pagBankSdk': {
            'deps': [
                'jquery'
            ]
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/action/place-order': {
                'RicardoMartins_PagBank/js/model/place-order-mixin': true
            }
        }
    }
};
