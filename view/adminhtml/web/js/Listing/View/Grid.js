define([
    'OnBuy/Grid',
    'OnBuy/Listing/View/Action'
], function () {

    window.ListingViewGrid = Class.create(Grid, {

        // ---------------------------------------

        productIdCellIndex: 1,
        productTitleCellIndex: 2,
        selectedProductsIds: [],

        // ---------------------------------------

        initialize: function ($super, gridId, listingId) {
            this.listingId = listingId;

            $super(gridId);
        },

        // ---------------------------------------

        getProductIdByRowId: function (rowId) {
            return this.getCellContent(rowId, this.productIdCellIndex);
        },

        // ---------------------------------------

        getSelectedItemsParts: function (maxProductsInPart) {
            let selectedProductsArray = this.getSelectedProductsArray();

            if (this.getSelectedProductsString() == '' || selectedProductsArray.length == 0) {
                return [];
            }

            maxProductsInPart = maxProductsInPart || this.getMaxProductsInPart();

            let result = [];
            for (let i = 0; i < selectedProductsArray.length; i++) {
                if (result.length == 0 || result[result.length - 1].length == maxProductsInPart) {
                    result[result.length] = [];
                }
                result[result.length - 1][result[result.length - 1].length] = selectedProductsArray[i];
            }

            return result;
        },

        // ---------------------------------------

        getMaxProductsInPart: function () {
            alert('abstract getMaxProductsInPart');
        },

        // ---------------------------------------

        prepareActions: function ($super) {
            this.actionHandler = new ListingViewAction(this);

            this.actions = {
                listAction: this.actionHandler.listAction.bind(this.actionHandler),
                relistAction: this.actionHandler.relistAction.bind(this.actionHandler),
                reviseAction: this.actionHandler.reviseAction.bind(this.actionHandler),
                stopAction: this.actionHandler.stopAction.bind(this.actionHandler),
                stopAndRemoveAction: this.actionHandler.stopAndRemoveAction.bind(this.actionHandler)
            };
        },

        // ---------------------------------------

        massActionSubmitClick: function ($super) {
            if (this.getSelectedProductsString() == '' || this.getSelectedProductsArray().length == 0) {
                this.alert(OnBuy.translator.translate('Please select the Products you want to perform the Action on.'));
                return;
            }
            $super();
        },

        // ---------------------------------------

        openPopUp: function (title, content, params, popupId) {
            const self = this;
            params = params || {};
            popupId = popupId || 'modal_view_action_dialog';

            let modalDialogMessage = $(popupId);

            if (!modalDialogMessage) {
                modalDialogMessage = new Element('div', {
                    id: popupId
                });
            }

            modalDialogMessage.innerHTML = '';

            this.popUp = jQuery(modalDialogMessage).modal(Object.extend({
                title: title,
                type: 'slide',
                buttons: [{
                    text: OnBuy.translator.translate('Cancel'),
                    attr: {id: 'cancel_button'},
                    class: 'action-dismiss',
                    click: function () {
                    }
                }, {
                    text: OnBuy.translator.translate('Confirm'),
                    attr: {id: 'done_button'},
                    class: 'action-primary action-accept forward',
                    click: function () {
                    }
                }],
                closed: function () {
                    self.selectedProductsIds = [];

                    self.getGridObj().reload();

                    return true;
                }
            }, params));

            this.popUp.modal('openModal');

            try {
                modalDialogMessage.innerHTML = content;
                modalDialogMessage.innerHTML.evalScripts();
            } catch (ignored) {
            }
        }

        // ---------------------------------------
    });

});
