define([
    'OnBuy/Common'
], function () {
    window.OnBuySettingsMain = Class.create(Common, {

        identifierSettings: {
            valueModeAttribute: ''
        },

        initialize: function (settings) {
            this.identifierSettings.valueModeAttribute = settings.identifierSettings.valueModeAttribute;

            jQuery.validator.addMethod('validator-required-when-visible', function (value, el) {
                return value > 0;
            }, 'This is a required field.');

            this.initObservers();
        },

        initObservers: function () {
            const self = this;

            $('identifier_code_mode').addEventListener('change', function () {
                self.identifier_code_mode_change(this);
            });
        },

        // ---------------------------------------

        identifier_code_mode_change: function (option) {
            $('identifier_code_custom_attribute').value = '';
            if (option.value == this.identifierSettings.valueModeAttribute) {
                this.updateHiddenValue(option, $('identifier_code_custom_attribute'));
            }
        }
    });
});
