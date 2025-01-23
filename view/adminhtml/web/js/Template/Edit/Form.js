define([
            'jquery',
            'OnBuy/Template/Edit'
        ],
        function (jQuery) {

            window.OnBuyTemplateEdit = Class.create(TemplateEdit, {

                // ---------------------------------------

                templateNick: null,

                // ---------------------------------------

                initialize: function () {
                    jQuery.validator.addMethod('validate-title-uniqueness', function (value) {

                        var unique = false;
                        var templateId = 0;

                        if ($(OnBuyTemplateEditObj.templateNick + '_id')) {
                            templateId = $(OnBuyTemplateEditObj.templateNick + '_id').value;
                        }

                        new Ajax.Request(OnBuy.url.get('policy/isTitleUnique'), {
                            method: 'post',
                            asynchronous: false,
                            parameters: {
                                id_value: templateId,
                                title: value
                            },
                            onSuccess: function (transport) {
                                unique = transport.responseText.evalJSON()['unique'];
                            }
                        });

                        return unique;
                    }, OnBuy.translator.translate('Policy Title is not unique.'));
                },

                // ---------------------------------------

                initObservers: function () {
                    this.loadTemplateData(null);
                },

                // ---------------------------------------

                loadTemplateData: function (callback) {
                    if (typeof this.value != 'undefined' && this.value === '') {
                        return;
                    }

                    var self = OnBuyTemplateEditObj;

                    new Ajax.Request(OnBuy.url.get('policy/getTemplateHtml'), {
                        method: 'get',
                        asynchronous: true,
                        parameters: {},
                        onSuccess: function (transport) {

                            var editFormData = $('edit_form_data');
                            if (!editFormData) {
                                editFormData = document.createElement('div');
                                editFormData.id = 'edit_form_data';

                                $('edit_form').appendChild(editFormData);
                            }

                            editFormData.innerHTML = transport.responseText;
                            editFormData.innerHTML.extractScripts()
                                    .map(function (script) {
                                        try {
                                            eval(script);
                                        } catch (e) {
                                        }
                                    });

                            var titleInput = $$('input[name="' + self.templateNick + '[title]"]')[0];

                            if ($('title').value.trim() == '') {
                                $('title').value = titleInput.value;
                            }

                            callback && callback();
                        }
                    });
                },

                // ---------------------------------------

                isValidForm: function () {
                    var validationResult = true;

                    validationResult &= jQuery('#edit_form').valid();
                    validationResult &= Validation.validate($('title'));

                    var titleInput = $$('input[name="' + OnBuyTemplateEditObj.templateNick + '[title]"]')[0];

                    if (titleInput) {
                        titleInput.value = $('title').value;
                    }

                    return validationResult;
                },

                // ---------------------------------------

                duplicateClick: function ($super, headId, chapter_when_duplicate_text, templateNick) {
                    $$('input[name="' + templateNick + '[id]"]')[0].value = '';

                    // we don't need it here, but parent method requires the formSubmitNew url to be defined
                    OnBuy.url.add({'formSubmitNew': ' '});

                    $super(headId, chapter_when_duplicate_text);
                },

                // ---------------------------------------

                saveAndCloseClick: function (url, confirmText) {
                    if (!this.isValidForm()) {
                        return;
                    }

                    var self = this;

                    if (confirmText && this.showConfirmMsg) {
                        this.confirm(this.templateNick, confirmText, function () {
                            self.saveFormUsingAjax(url, self.templateNick);
                        });
                        return;
                    }

                    self.saveFormUsingAjax(url, self.templateNick);
                },

                saveFormUsingAjax: function (url, templateNick) {
                    new Ajax.Request(url, {
                        method: 'post',
                        parameters: Form.serialize($('edit_form')),
                        onSuccess: function (transport) {
                            var templates = transport.responseText.evalJSON();

                            if (templates.length && templates[0].nick == templateNick) {
                                window.close();

                            } else {
                                console.error('Policy Saving Error');
                            }
                        }
                    });
                }

                // ---------------------------------------
            });
        });
