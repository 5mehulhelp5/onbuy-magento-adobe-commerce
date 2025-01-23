<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\ScheduledAction;

use M2E\OnBuy\Model\ResourceModel\ScheduledAction as ScheduledActionResource;

class Repository
{
    /** how much time should pass to increase priority value by 1 */
    private const SECONDS_TO_INCREMENT_PRIORITY = 30;

    private \M2E\OnBuy\Model\ResourceModel\ScheduledAction $resource;
    private ScheduledActionResource\CollectionFactory $collectionFactory;
    private \M2E\OnBuy\Model\ResourceModel\Listing $listingResource;
    private \M2E\OnBuy\Model\ResourceModel\Product $productResource;

    public function __construct(
        \M2E\OnBuy\Model\ResourceModel\Listing $listingResource,
        \M2E\OnBuy\Model\ResourceModel\Product $productResource,
        \M2E\OnBuy\Model\ResourceModel\ScheduledAction $resource,
        ScheduledActionResource\CollectionFactory $collectionFactory
    ) {
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
        $this->listingResource = $listingResource;
        $this->productResource = $productResource;
    }

    public function create(\M2E\OnBuy\Model\ScheduledAction $action): void
    {
        $this->resource->save($action);
    }

    /**
     * @param \M2E\OnBuy\Model\ScheduledAction[] $ids
     *
     * @return array
     */
    public function getByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(ScheduledActionResource::COLUMN_ID, array_unique($ids));

        return array_values($collection->getItems());
    }

    public function findByListingProductId(int $listingProductId): ?\M2E\OnBuy\Model\ScheduledAction
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(ScheduledActionResource::COLUMN_LISTING_PRODUCT_ID, $listingProductId);

        /** @var \M2E\OnBuy\Model\ScheduledAction $item */
        $item = $collection->getFirstItem();
        if ($item->isObjectNew()) {
            return null;
        }

        return $item;
    }

    public function remove(\M2E\OnBuy\Model\ScheduledAction $action): void
    {
        $this->resource->delete($action);
    }

    public function createCollectionForFindByActionType(int $priority, int $actionType)
    {
        $collection = $this->collectionFactory->create();

        $collection->getSelect()->joinLeft(
            ['lp' => $this->productResource->getMainTable()],
            'main_table.listing_product_id = lp.id'
        );
        $collection->getSelect()->joinLeft(
            ['l' => $this->listingResource->getMainTable()],
            'lp.listing_id = l.id'
        );

        $collection->addFieldToFilter('main_table.action_type', $actionType);

        $now = \M2E\Core\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s');
        $collection->getSelect()
                   ->reset(\Magento\Framework\DB\Select::COLUMNS)
                   ->columns(
                       [
                           'id' => sprintf('main_table.%s', ScheduledActionResource::COLUMN_ID),
                           'listing_product_id' => sprintf(
                               'main_table.%s',
                               ScheduledActionResource::COLUMN_LISTING_PRODUCT_ID
                           ),
                           'account_id' => 'l.account_id',
                           'action_type' => sprintf('main_table.%s', ScheduledActionResource::COLUMN_ACTION_TYPE),
                           'tag' => new \Zend_Db_Expr('NULL'),
                           'additional_data' => sprintf(
                               'main_table.%s',
                               ScheduledActionResource::COLUMN_ADDITIONAL_DATA
                           ),
                           'coefficient' => new \Zend_Db_Expr(
                               "$priority +
                        (time_to_sec(timediff('$now', main_table.create_date)) / "
                               . self::SECONDS_TO_INCREMENT_PRIORITY . ')'
                           ),
                           'create_date' => 'create_date',
                       ]
                   );

        return $collection;
    }
}
