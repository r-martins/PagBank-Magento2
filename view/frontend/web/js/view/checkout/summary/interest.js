define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals'
    ],
    function (Component, quote, priceUtils, totals) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'RicardoMartins_PagBank/checkout/summary/interest',
                active: true
            },
            totals: quote.getTotals(),

            // initObservable() {
            //     this._super().observe(['active']);
            //     return this;
            // },

            isDisplayed: function() {
                return this.getPureValue() !== 0;
            },

            getPureValue: function() {
                let interest = 0;

                if (this.totals() && totals.getSegment('ricardomartins_pagbank_interest_amount')) {
                    interest = totals.getSegment('ricardomartins_pagbank_interest_amount').value;
                }

                return interest;
            },

            getValue: function() {
                return this.getFormattedPrice(this.getPureValue());
            },
        });
    }
);
