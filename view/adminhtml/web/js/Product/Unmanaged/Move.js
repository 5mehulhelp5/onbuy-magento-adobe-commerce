define([
    'OnBuy/Product/Unmanaged/Move/RetrieveSelected',
    'OnBuy/Product/Unmanaged/Move/PrepareProducts',
    'OnBuy/Product/Unmanaged/Move/Processor',
], (RetrieveSelected, PrepareProducts, MoveProcess) => {
    'use strict';

    return {
        startMoveForProduct: (id, urlPrepareMove, urlGrid, urlListingCreate, accountId) => {
            PrepareProducts.prepareProducts(
                    urlPrepareMove,
                    [id],
                    accountId,
                    function (siteId) {
                        MoveProcess.openMoveToListingGrid(
                                urlGrid,
                                urlListingCreate,
                                accountId,
                                siteId
                        );
                    }
            );
        },

        startMoveForProducts: (massActionData, urlPrepareMove, urlGrid, urlGetSelectedProducts, urlListingCreate, accountId) => {
            RetrieveSelected.getSelectedProductIds(
                    massActionData,
                    urlGetSelectedProducts,
                    accountId,
                    function (selectedProductIds) {
                        PrepareProducts.prepareProducts(
                                urlPrepareMove,
                                selectedProductIds,
                                accountId,
                                function (siteId) {
                                    MoveProcess.openMoveToListingGrid(
                                            urlGrid,
                                            urlListingCreate,
                                            accountId,
                                            siteId
                                    );
                                }
                        );
                    }
            );
        }
    };
});
