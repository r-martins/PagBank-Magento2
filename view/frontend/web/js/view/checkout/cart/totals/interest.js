define(
    [
        'RicardoMartins_PagBank/js/view/checkout/summary/interest'
    ],
    function (Component) {
        'use strict';

        return Component.extend({

            /**
             * @override
             */
            isDisplayed: function () {
                return this.getPureValue() !== 0;
            }
        });
    }
);
