define([
    'mage/translate',
    'Magento_Ui/js/modal/modal',
    'M2ECore/Plugin/Messages'
], function ($t, modal, MessageObj) {

    window.UploadByUser = Class.create(Common, {

        gridId: null,
        messageManager: null,

        //---------------------------------------

        initialize: function (gridId) {
            this.gridId = gridId;

            this.messageManager = MessageObj;
            this.messageManager.setContainer('#uploadByUser_messages');
        },

        //---------------------------------------

        openPopup: function () {
            new Ajax.Request(OnBuy.url.get('order_uploadByUser/getPopupHtml'), {
                method: 'post',
                parameters: {},
                onSuccess: function (transport) {

                    if (!$('orders_upload_by_user_modal')) {
                        $('html-body').insert({bottom: '<div id="orders_upload_by_user_modal"></div>'});
                    }

                    var modalBlock = $('orders_upload_by_user_modal');
                    modalBlock.update(transport.responseText);

                    var popup = jQuery(modalBlock).modal({
                        title: $t('Order Reimport'),
                        type: 'popup',
                        modalClass: 'width-100',
                        buttons: [{
                            text: $t('Close'),
                            class: 'action-secondary action-dismiss',
                            click: function () {
                                this.closePopup();
                            }.bind(this)
                        }]
                    });

                    popup.modal('openModal');

                }.bind(this)
            });
        },

        closePopup: function () {
            jQuery('#orders_upload_by_user_modal').modal('closeModal');
        },

        //----------------------------------------

        reloadGrid: function () {
            window[this.gridId + 'JsObject'].reload();
        },

        //----------------------------------------

        resetUpload: function (accountId, siteId) {
            new Ajax.Request(OnBuy.url.get('order_uploadByUser/reset'), {
                method: 'post',
                parameters: {
                    account_id: accountId,
                    site_id: siteId
                },
                onSuccess: function (transport) {
                    var json = this.processJsonResponse(transport.responseText);
                    if (json === false) {
                        return;
                    }

                    this.reloadGrid();

                    if (json.result) {
                        this.messageManager.addSuccess($t('Order importing is canceled.'));
                    }
                }.bind(this)
            });
        },

        configureUpload: function (accountId, siteId) {
            var fromId = accountId + '_' + siteId;

            this.initFormValidation('#' + fromId + '_form');
            if (!jQuery('#' + fromId + '_form').valid()) {
                return;
            }

            new Ajax.Request(OnBuy.url.get('order_uploadByUser/configure'), {
                method: 'post',
                parameters: {
                    account_id: accountId,
                    site_id: siteId,
                    from_date: $(fromId).value
                },
                onSuccess: function (transport) {
                    var json = this.processJsonResponse(transport.responseText);
                    if (json === false) {
                        return;
                    }

                    this.reloadGrid();

                    if (json.result) {
                        this.messageManager.addSuccess($t('Order importing in progress.'));
                    }
                }.bind(this)
            });
        },

        // ---------------------------------------

        processJsonResponse: function (responseText) {
            if (!responseText.isJSON()) {
                alert(responseText);
                return false;
            }

            var response = responseText.evalJSON();
            if (typeof response.result === 'undefined') {
                alert('Invalid response.');
                return false;
            }

            this.messageManager.clearAll();
            if (typeof response.messages !== 'undefined') {
                response.messages.each(function (msg) {
                    this.messageManager['add' + msg.type[0].toUpperCase() + msg.type.slice(1)](msg.text);
                }.bind(this));
            }

            return response;
        }

        // ---------------------------------------
    });
});
