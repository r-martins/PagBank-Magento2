define([], function () {
    'use strict';

    return {
        /**
         * @param {String|null|undefined} value
         * @returns {String}
         */
        normalize: function (value) {
            if (!value) {
                return '';
            }

            return String(value)
                .replace(/[.\-/]/g, '')
                .replace(/[^A-Za-z0-9]/g, '')
                .toUpperCase();
        },

        /**
         * @param {String} normalized
         * @returns {Boolean}
         */
        isCpf: function (normalized) {
            return normalized.length <= 11 && /^[0-9]*$/.test(normalized);
        },

        /**
         * @param {String|null|undefined} value
         * @returns {String}
         */
        mask: function (value) {
            let raw = this.normalize(value);

            if (this.isCpf(raw)) {
                raw = raw.slice(0, 11);

                return raw
                    .replace(/(\d{3})(\d)/, '$1.$2')
                    .replace(/(\d{3})(\d)/, '$1.$2')
                    .replace(/(\d{3})(\d{1,2})/, '$1-$2')
                    .replace(/(-\d{2})\d+?$/, '$1');
            }

            raw = raw.slice(0, 14);

            return raw
                .replace(/([A-Z0-9]{2})([A-Z0-9])/, '$1.$2')
                .replace(/([A-Z0-9]{3})([A-Z0-9])/, '$1.$2')
                .replace(/([A-Z0-9]{3})([A-Z0-9])/, '$1/$2')
                .replace(/([A-Z0-9]{4})([A-Z0-9])/, '$1-$2')
                .replace(/(-[A-Z0-9]{2})[A-Z0-9]+?$/, '$1');
        }
    };
});
