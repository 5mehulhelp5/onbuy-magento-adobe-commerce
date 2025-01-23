define([
    'jquery',
    'OnBuy/Url',
    'OnBuy/Php',
    'OnBuy/Translator',
    'OnBuy/Common',
    'prototype',
    'OnBuy/Plugin/BlockNotice',
    'OnBuy/Plugin/Prototype/Event.Simulate',
    'OnBuy/Plugin/Fieldset',
    'OnBuy/Plugin/Validator',
    'OnBuy/General/PhpFunctions',
    'mage/loader_old'
], function (jQuery, Url, Php, Translator) {

    jQuery('body').loader();

    Ajax.Responders.register({
        onException: function (event, error) {
            console.error(error);
        }
    });

    return {
        url: Url,
        php: Php,
        translator: Translator
    };

});
