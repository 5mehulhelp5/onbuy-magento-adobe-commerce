define([
    'M2ECore/Plugin/Messages',
], function (MessageObj) {

    window.WizardInstallationOnBuy = Class.create(Common, {

        continueStep: function () {
            if (WizardObj.steps.current.length) {
                this[WizardObj.steps.current + 'Step']();
            }
        },

        // Steps
        // ---------------------------------------

        registrationStep: function () {
            WizardObj.registrationStep(OnBuy.url.get('wizard_registration/createLicense'));
        },

        accountStep: function () {
            if (!this.isValidForm()) {
                return false;
            }

            new Ajax.Request(OnBuy.url.get('wizard_installationOnBuy/accountCreate'), {
                method: 'post',
                asynchronous: true,
                parameters: $('edit_form').serialize(),
                onSuccess: function (transport) {

                    var response = transport.responseText.evalJSON();

                    if (response && response['message']) {
                        MessageObj.addError(response['message']);
                        return CommonObj.scrollPageToTop();
                    }

                    if (!response['url']) {
                        MessageObj.addError(OnBuy.translator.translate('An error during of account creation.'));
                        return CommonObj.scrollPageToTop();
                    }

                    return setLocation(response['url']);
                }
            });
        },

        settingsStep: function () {
            this.initFormValidation();

            if (!this.isValidForm()) {
                return false;
            }

            this.submitForm(OnBuy.url.get('wizard_installationOnBuy/settingsContinue'));
        },

        listingTutorialStep: function () {
            WizardObj.setStep(WizardObj.getNextStep(), function () {
                WizardObj.complete();
            });
        }

        // ---------------------------------------
    });
});
