define([
    'jquery',
    'RicardoMartins_PagBank/js/model/document-normalizer',
    'RicardoMartins_PagBank/js/model/payment-validation/pagbank-customer-data'
], function ($, documentNormalizer, pagbankCustomerData) {
    'use strict';

    return {
        /**
         * @param {Object} component Payment method renderer with getCode() and taxId observable
         */
        bind: function (component) {
            component.taxId.subscribe(function (value) {
                const documentField = $('#' + component.getCode() + '_tax_id');
                const masked = documentNormalizer.mask(value);

                if (masked !== value) {
                    documentField.val(masked);
                    component.taxId(masked);
                    return;
                }

                pagbankCustomerData.taxId = documentNormalizer.normalize(value);
            });
        }
    };
});
