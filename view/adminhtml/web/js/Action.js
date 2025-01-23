define([
    'OnBuy/Common'
], function () {

    window.Action = Class.create(Common, {

        // ---------------------------------------

        initialize: function (gridHandler) {
            this.gridHandler = gridHandler;
        }

        // ---------------------------------------
    });
});
