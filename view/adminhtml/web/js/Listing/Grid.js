define([
    'OnBuy/Grid',
    'prototype'
], function () {

    window.OnBuyListingGrid = Class.create(Grid, {

        // ---------------------------------------

        backParam: base64_encode('*/onbuy_listing/index'),

        // ---------------------------------------

        prepareActions: function () {
            return false;
        },

        // ---------------------------------------

        addProductsSourceProductsAction: function (id) {
            setLocation(OnBuy.url.get('listing_product_add/index', {
                id: id,
                source: 'product',
                clear: true,
                back: this.backParam
            }));
        },

        // ---------------------------------------

        addProductsSourceCategoriesAction: function (id) {
            setLocation(OnBuy.url.get('listing_product_add/index', {
                id: id,
                source: 'category',
                clear: true,
                back: this.backParam
            }));
        }

        // ---------------------------------------
    });

});
