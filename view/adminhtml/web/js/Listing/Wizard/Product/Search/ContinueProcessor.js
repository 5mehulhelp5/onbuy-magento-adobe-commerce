define([
    'jquery',
    'mage/storage',
    'mage/translate',
    'Magento_Ui/js/modal/modal'
], function ($, storage, $t, modal) {
    'use strict';

    return function (options, continueButton) {
        const processor = {
            urlCheck: options.urlCheck,
            urlContinue: options.urlContinue,

            process: function () {
                storage.get(this.urlCheck)
                        .done(this.processCheckResult.bind(this));
            },

            processCheckResult: function (response) {
                const isSearchCompleted = response.is_search_completed;
                if (!isSearchCompleted) {
                    alert($t('Please configure the EAN setting to complete the Product search on OnBuy.'));

                    return;
                }

                this.goFrom();
            },

            goFrom: function () {
                window.location.href = this.urlContinue;
            }
        };

        $(continueButton).on('click', function (e) {
            e.preventDefault();
            processor.process();
        });
    };
});
