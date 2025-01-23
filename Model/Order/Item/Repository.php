<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Order\Item;

use M2E\OnBuy\Model\ResourceModel\Order\Item as OrderItemResource;

class Repository
{
    private \M2E\OnBuy\Model\ResourceModel\Order\Item\CollectionFactory $collectionFactory;
    private OrderItemResource $resource;
    private \M2E\OnBuy\Model\Order\ItemFactory $itemFactory;

    public function __construct(
        \M2E\OnBuy\Model\Order\ItemFactory $itemFactory,
        \M2E\OnBuy\Model\ResourceModel\Order\Item\CollectionFactory $collectionFactory,
        \M2E\OnBuy\Model\ResourceModel\Order\Item $resource
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
        $this->itemFactory = $itemFactory;
    }

    public function find(int $id): ?\M2E\OnBuy\Model\Order\Item
    {
        $item = $this->itemFactory->createEmpty();
        $this->resource->load($item, $id);

        if ($item->isObjectNew()) {
            return null;
        }

        return $item;
    }

    /**
     * @return \M2E\OnBuy\Model\Order\Item[]
     */
    public function getByIds(array $ids): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(OrderItemResource::COLUMN_ID, ['in' => $ids]);

        return array_values($collection->getItems());
    }

    public function create(\M2E\OnBuy\Model\Order\Item $orderItem): void
    {
        $this->resource->save($orderItem);
    }

    public function save(\M2E\OnBuy\Model\Order\Item $orderItem): void
    {
        $this->resource->save($orderItem);
    }

    public function remove(\M2E\OnBuy\Model\Order\Item $orderItem): void
    {
        $this->resource->delete($orderItem);
    }

    public function findByOrder(\M2E\OnBuy\Model\Order $order): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(OrderItemResource::COLUMN_ORDER_ID, ['eq' => $order->getId()]);

        $result = [];
        foreach ($collection->getItems() as $item) {
            $item->initOrder($order);

            $result[] = $item;
        }

        return $result;
    }

    public function findByOrderIdAndSku(int $orderId, string $sku): ?\M2E\OnBuy\Model\Order\Item
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(OrderItemResource::COLUMN_ORDER_ID, ['eq' => $orderId]);
        $collection->addFieldToFilter(OrderItemResource::COLUMN_PRODUCT_SKU, ['eq' => $sku]);

        $item = $collection->getFirstItem();

        if ($item->isObjectNew()) {
            return null;
        }

        return $item;
    }

    public function getOrderItemCollection(int $orderId): \M2E\OnBuy\Model\ResourceModel\Order\Item\Collection
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(OrderItemResource::COLUMN_ORDER_ID, ['eq' => $orderId]);

        return $collection;
    }

    public function getOrderItemCollectionByOrderIds(array $orderIds): \M2E\OnBuy\Model\ResourceModel\Order\Item\Collection
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(OrderItemResource::COLUMN_ORDER_ID, ['in' => $orderIds]);

        return $collection;
    }

    public function getOrderIdsBySearchValue(string $value): array
    {
        $collection = $this->collectionFactory->create();

        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $collection->getSelect()->columns(OrderItemResource::COLUMN_ORDER_ID);
        $collection->getSelect()->distinct();

        $collection->addFieldToFilter(
            [
                OrderItemResource::COLUMN_PRODUCT_TITLE,
                OrderItemResource::COLUMN_PRODUCT_SKU,
                OrderItemResource::COLUMN_CHANNEL_PRODUCT_ID
            ],
            [
                ['like' => "%$value%"],
                ['like' => "%$value%"],
                ['like' => "%$value%"]
            ]
        );

        return $collection->getColumnValues(OrderItemResource::COLUMN_ORDER_ID);
    }
}
