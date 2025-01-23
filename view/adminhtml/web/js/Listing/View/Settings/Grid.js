define([
    'jquery',
    'OnBuy/Listing/View/Grid',
    'OnBuy/Listing/MovingFromListing',
    'Magento_Ui/js/modal/modal'
], function (jQuery) {

    window.OnBuyListingViewSettingsGrid = Class.create(ListingViewGrid, {

        // ---------------------------------------

        accountId: null,
        shopId: null,

        // ---------------------------------------

        initialize: function ($super, gridId, listingId, accountId, shopId) {
            this.accountId = accountId;
            this.shopId = shopId;

            $super(gridId, listingId);
        },

        // ---------------------------------------

        prepareActions: function ($super) {
            $super();

            this.movingHandler = new MovingFromListing(this);

            this.actions = Object.extend(this.actions, {
                movingAction: this.movingHandler.run.bind(this.movingHandler),
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
