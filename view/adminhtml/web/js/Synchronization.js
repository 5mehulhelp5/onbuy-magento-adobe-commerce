define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'M2ECore/Plugin/Messages',
    'OnBuy/Common'
], function (jQuery, modal, MessageObj) {

    window.Synchronization = Class.create(Common, {

        // ---------------------------------------

        saveSettings: function () {
            MessageObj.clear();
            CommonObj.scrollPageToTop();

            new Ajax.Request(OnBuy.url.get('synch_formSubmit'), {
                method: 'post',
                parameters: {
                    instructions_mode: $('instructions_mode').value
                },
                asynchronous: true,
                onSuccess: function (transport) {
                    MessageObj.addSuccess(OnBuy.translator.translate('Synchronization Settings have been saved.'));
                }
            });
        }

        // ---------------------------------------
    });
});
