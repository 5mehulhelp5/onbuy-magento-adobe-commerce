define([
    'jquery',
    'mage/translate',
], function ($, $t) {
    'use strict';

    return function (options, continueButton) {
        const processor = {
            urlSave: options.urlSave,
            urlContinue: options.urlContinue,

            saveSettings: function () {
                const item = $('#identifier_settings');
                let form = $(item).find('form');

                let formData = {};

                form.find(':input').each(function () {
                    let name = $(this).attr('name');
                    let value = $(this).val();

                    if (name) {
                        formData[name] = value;
                    }
                });

                const self = this;

                $.ajax({
                    url: this.urlSave,
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function (response) {
                        if (!response.result) {
                            alert($t('Please configure the Identifier setting to complete the Product search on OnBuy.'));

                            return;
                        }
                        self.goFrom()
                    },
                });
            },

            goFrom: function () {
                window.location.href = this.urlContinue;
            }
        };

        $(continueButton).on('click', function () {
            processor.saveSettings();
        });
    };
});
