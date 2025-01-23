<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Listing;

class AffectedListingsProducts extends \M2E\OnBuy\Model\Policy\AffectedListingsProductsAbstract
{
    private \M2E\OnBuy\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory;

    public function __construct(
        \M2E\OnBuy\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory
    ) {
        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
    }

    public function loadListingProductCollection(
        array $filters = []
    ): \M2E\OnBuy\Model\ResourceModel\Product\Collection {
        $collection = $this->listingProductCollectionFactory->create();
        $collection->addFieldToFilter(
            \M2E\OnBuy\Model\ResourceModel\Product::COLUMN_LISTING_ID,
            $this->getModel()->getId()
        );

        return $collection;
    }
}
