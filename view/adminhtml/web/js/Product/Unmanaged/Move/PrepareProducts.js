define([
    'jquery',
], ($) => {
    'use strict';

    return {
        prepareProducts: function(url, productsIds, accountId, callback) {
            $.ajax(
                    {
                        url: url,
                        type: 'POST',
                        data: {
                            unmanaged_product_ids: productsIds,
                            account_id: accountId
                        },
                        dataType: 'json',
                        success: function(data) {
                            callback(data.siteId);
                        },
                    }
            );
        },
    };
});
