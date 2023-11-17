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
    }
};
