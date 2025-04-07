define([
    'OnBuy/Common'
], function () {

    window.SelectedProductsData = Class.create(Common, {

        // ---------------------------------------

        wizardId: null,
        productId: null,
        siteId: null,

        // ---------------------------------------

        getWizardId: function () {
            return this.wizardId;
        },

        setWizardId: function (id) {
            this.wizardId = id;
        },

        // ---------------------------------------

        getSiteId: function () {
            return this.siteId;
        },

        setSiteId: function (siteId) {
            this.siteId = siteId;
        },

        // ---------------------------------------

        getProductId: function () {
            return this.productId;
        },

        setProductId: function (id) {
            this.productId = id;
        }
    });
});
