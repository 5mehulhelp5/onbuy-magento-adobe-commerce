define([
    'jquery',
    'OnBuy/Listing/View/Grid',
    'OnBuy/Listing/Wizard/Category',
    'OnBuy/Listing/MovingFromListing',
    'Magento_Ui/js/modal/modal'
], function (jQuery) {
//TODO: add listing wizard category
    window.OnBuyListingViewSettingsGrid = Class.create(ListingViewGrid, {

        // ---------------------------------------

        accountId: null,
        siteId: null,

        // ---------------------------------------

        initialize: function ($super, gridId, listingId, accountId, siteId) {
            this.accountId = accountId;
            this.siteId = siteId;

            $super(gridId, listingId);
        },

        // ---------------------------------------

        prepareActions: function ($super) {
            $super();

            this.movingHandler = new MovingFromListing(this);
            this.categoryHandler = new OnBuyListingCategory(this);

            this.actions = Object.extend(this.actions, {
                movingAction: this.movingHandler.run.bind(this.movingHandler),
                editCategorySettingsAction: this.categoryHandler.editCategorySettings.bind(this.categoryHandler)
            });
        },

        // ---------------------------------------

        tryToMove: function (listingId) {
            this.movingHandler.submit(listingId, this.onSuccess)
        },

        onSuccess: function () {
            this.unselectAllAndReload();
        },

        // ---------------------------------------

        confirm: function (config) {
            if (config.actions && config.actions.confirm) {
                config.actions.confirm();
            }
        },

        // ---------------------------------------
    });
});
