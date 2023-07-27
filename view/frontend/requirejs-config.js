var config = {
    paths: {
        'pagBankSdk': [
            'https://assets.pagseguro.com.br/checkout-sdk-js/rc/dist/browser/pagseguro',
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
