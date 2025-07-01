<?php

namespace M2E\OnBuy\Model\ResourceModel\Listing;

use Magento\Framework\Event\ManagerInterface;

/**
 * @method \M2E\OnBuy\Model\Listing[] getItems()
 * @method \M2E\OnBuy\Model\Listing[] getFirstItem()
 */
class Collection extends \M2E\OnBuy\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    private \M2E\OnBuy\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory;

    public function __construct(
        \M2E\OnBuy\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        ?\Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        ?\Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(
            \M2E\OnBuy\Model\Listing::class,
            \M2E\OnBuy\Model\ResourceModel\Listing::class
        );
    }

    public function addProductsTotalCount(): self
    {
        $collection = $this->listingProductCollectionFactory->create();
        $collection->addFieldToSelect(\M2E\OnBuy\Model\ResourceModel\Product::COLUMN_LISTING_ID);
        $collection->addExpressionFieldToSelect(
            'products_total_count',
            'COUNT({{id}})',
            ['id' => \M2E\OnBuy\Model\ResourceModel\Product::COLUMN_ID]
        );
        $collection->getSelect()->group(\M2E\OnBuy\Model\ResourceModel\Product::COLUMN_LISTING_ID);

        $this->getSelect()->joinLeft(
            ['t' => $collection->getSelect()],
            'main_table.id=t.listing_id',
            [
                'products_total_count' => 'products_total_count',
            ]
        );

        return $this;
    }
}
